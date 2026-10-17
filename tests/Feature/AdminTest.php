<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Database\Seeders\DemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Testing\File;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DemoSeeder::class);
    }

    private function makeUser(string $role): User
    {
        return User::create(['name' => $role, 'email' => $role.'@example.com', 'password' => 'password-12345', 'role' => $role, 'is_active' => true]);
    }

    private function placeOrder(): Order
    {
        $product = Product::published()->firstOrFail();
        $this->post('/cart', ['product_id' => $product->id, 'quantity' => 1]);
        $this->post('/checkout', ['name' => 'A', 'phone' => '01711111111', 'address_line1' => 'H1', 'delivery_zone' => 'inside_dhaka', 'idempotency_key' => uniqid('k')]);

        return Order::where('customer_phone', '01711111111')->latest()->firstOrFail();
    }

    public function test_guests_cannot_access_admin(): void
    {
        foreach (['/admin', '/admin/orders', '/admin/products', '/admin/inventory', '/admin/settings', '/admin/users'] as $url) {
            $this->get($url)->assertRedirect('/admin/login');
        }
    }

    public function test_order_manager_can_manage_orders_but_nothing_else(): void
    {
        $staff = $this->makeUser('order_manager');
        $order = $this->placeOrder();

        $this->actingAs($staff)->get('/admin')->assertOk();
        $this->actingAs($staff)->get('/admin/orders')->assertOk();
        $this->actingAs($staff)->get('/admin/orders/'.$order->id)->assertOk();

        // Direct URL permission boundaries
        $this->actingAs($staff)->get('/admin/products')->assertForbidden();
        $this->actingAs($staff)->get('/admin/inventory')->assertForbidden();
        $this->actingAs($staff)->get('/admin/settings')->assertForbidden();
        $this->actingAs($staff)->get('/admin/users')->assertForbidden();
        $this->actingAs($staff)->post('/admin/inventory/adjust', ['product_id' => 1, 'change_qty' => 5, 'reason' => 'adjustment'])->assertForbidden();
        $this->actingAs($staff)->post('/admin/products', ['title' => 'x', 'sku' => 'x', 'status' => 'draft', 'base_price' => 1, 'stock_quantity' => 1, 'low_stock_threshold' => 1])->assertForbidden();

        // Valid status transition works + is audited
        $this->actingAs($staff)->patch('/admin/orders/'.$order->id.'/status', ['status' => 'confirmed'])->assertRedirect();
        $this->assertEquals('confirmed', $order->fresh()->status);
        $this->assertDatabaseHas('order_status_history', ['order_id' => $order->id, 'to_status' => 'confirmed']);
        $this->assertDatabaseHas('audit_logs', ['action' => 'order.status', 'auditable_id' => $order->id]);
    }

    public function test_invalid_status_transition_rejected(): void
    {
        $owner = $this->makeUser('owner');
        $order = $this->placeOrder();
        // pending → delivered skips the chain
        $this->actingAs($owner)->patch('/admin/orders/'.$order->id.'/status', ['status' => 'delivered'])->assertRedirect();
        $this->assertEquals('pending', $order->fresh()->status);
    }

    public function test_shipped_decrements_inventory_and_cancel_restores(): void
    {
        $owner = $this->makeUser('owner');
        $order = $this->placeOrder();
        $productId = $order->items->first()->product_id;
        $product = Product::findOrFail($productId);
        $stockBefore = $product->stock_quantity;
        $qty = (int) $order->items->first()->quantity;

        // Checkout reserves only — no deduction yet.
        $this->assertEquals($stockBefore, $product->fresh()->stock_quantity);

        // pending → confirmed → shipped
        $this->actingAs($owner)->patch('/admin/orders/'.$order->id.'/status', ['status' => 'confirmed'])->assertRedirect();
        $this->actingAs($owner)->patch('/admin/orders/'.$order->id.'/status', ['status' => 'shipped'])->assertRedirect();
        $this->assertEquals('shipped', $order->fresh()->status);
        $this->assertEquals($stockBefore - $qty, $product->fresh()->stock_quantity);
        $this->assertNotNull($order->fresh()->stock_deducted_at);
        $this->assertDatabaseHas('stock_movements', ['product_id' => $productId, 'change_qty' => -$qty, 'reason' => 'sale']);

        // shipped → cancelled restores
        $this->actingAs($owner)->patch('/admin/orders/'.$order->id.'/status', ['status' => 'cancelled'])->assertRedirect();
        $this->assertEquals('cancelled', $order->fresh()->status);
        $this->assertEquals($stockBefore, $product->fresh()->stock_quantity);
        $this->assertDatabaseHas('stock_movements', ['product_id' => $productId, 'change_qty' => $qty, 'reason' => 'cancelled']);
    }

    public function test_ship_blocked_when_insufficient_stock(): void
    {
        $owner = $this->makeUser('owner');
        $order = $this->placeOrder();
        $productId = $order->items->first()->product_id;
        $qty = (int) $order->items->first()->quantity;
        // Staff sells the stock manually before shipping.
        Product::where('id', $productId)->update(['stock_quantity' => $qty - 1]);

        $this->actingAs($owner)->patch('/admin/orders/'.$order->id.'/status', ['status' => 'confirmed'])->assertRedirect();
        $this->actingAs($owner)->patch('/admin/orders/'.$order->id.'/status', ['status' => 'shipped'])->assertRedirect();
        $this->assertEquals('confirmed', $order->fresh()->status);
        $this->assertNull($order->fresh()->stock_deducted_at);
    }

    public function test_admin_can_manage_products_but_not_users(): void
    {
        $admin = $this->makeUser('admin');
        $this->actingAs($admin)->get('/admin/products')->assertOk();
        $this->actingAs($admin)->get('/admin/inventory')->assertOk();
        $this->actingAs($admin)->get('/admin/settings')->assertOk();
        $this->actingAs($admin)->get('/admin/users')->assertForbidden();
    }

    public function test_owner_last_owner_protection(): void
    {
        $owner = $this->makeUser('owner');
        $this->actingAs($owner)->patch('/admin/users/'.$owner->id, ['role' => 'admin', 'is_active' => 1])->assertRedirect();
        $this->assertEquals('owner', $owner->fresh()->role);
    }

    public function test_product_delete_blocked_when_in_orders(): void
    {
        $owner = $this->makeUser('owner');
        $order = $this->placeOrder();
        $productId = $order->items->first()->product_id;
        $this->actingAs($owner)->delete('/admin/products/'.$productId)->assertRedirect();
        $this->assertDatabaseHas('products', ['id' => $productId]);
    }

    public function test_login_rate_limiting_and_validation(): void
    {
        $this->makeUser('owner');
        // 4 bad attempts stay under the 5-attempt custom limiter; each shows invalid-credentials error
        for ($i = 0; $i < 4; $i++) {
            $this->post('/admin/login', ['email' => 'owner@example.com', 'password' => 'wrong'])
                ->assertRedirect()
                ->assertSessionHasErrors('email');
        }

        // Good login still works before the limit
        $this->post('/admin/login', ['email' => 'owner@example.com', 'password' => 'password-12345'])->assertRedirect('/admin');
        $this->assertAuthenticated();
        $this->post('/admin/logout');

        // Hammer past the limit: 6th bad attempt triggers the throttle message
        for ($i = 0; $i < 5; $i++) {
            $this->post('/admin/login', ['email' => 'owner@example.com', 'password' => 'wrong']);
        }
        $response = $this->post('/admin/login', ['email' => 'owner@example.com', 'password' => 'wrong']);
        $response->assertRedirect();
        $response->assertSessionHasErrors('email');
        $this->assertStringContainsString('Too many login attempts', (string) session('errors')->first('email'));
    }

    public function test_csv_import_endpoint_works(): void
    {
        $this->actingAs($this->makeUser('owner'));

        $rows = [
            ['sku', 'title', 'base_price', 'stock_quantity', 'variant_sku', 'variant_name', 'variant_price_override', 'variant_stock_quantity', 'variant_is_active'],
            ['IMP-0001', 'Imported Test Product', '1000.00', '50'],
            ['IMP-0002', 'Imported Variant Set', '1350.00', '100', 'IMP-0002-M', 'Size M', '1450.00', '30', '1'],
        ];

        $csv = implode("\n", array_map(function (array $row) {
            return implode(',', array_map(function (string $v) {
                return '"'.str_replace('"', '""', $v).'"';
            }, $row));
        }, $rows));

        $response = $this->post('/admin/csv-import/upload', [
            'file' => $this->prepareCsvFile($csv),
            'category_slug' => '',
        ]);

        $response->assertRedirect();
        $this->assertCount(2, Product::whereIn('sku', ['IMP-0001', 'IMP-0002'])->get());
        $this->assertCount(1, ProductVariant::where('sku', 'IMP-0002-M')->get());
    }

    private function prepareCsvFile(string $csv): File
    {
        return File::createWithContent('products.csv', $csv);
    }

    public function test_health_endpoint_reveals_no_secrets(): void
    {
        $response = $this->get('/health');
        $response->assertStatus(200);
        $body = $response->getContent();
        $this->assertStringContainsString('"status":"ok"', $body);
        $this->assertStringNotContainsStringIgnoringCase('APP_KEY', $body);
        $this->assertStringNotContainsStringIgnoringCase('password', $body);
    }

    public function test_sitemap_and_robots(): void
    {
        $this->get('/sitemap.xml')->assertOk();
        $robots = $this->get('/robots.txt')->assertOk()->getContent();
        $this->assertStringContainsString('Disallow: /', $robots); // staging default
    }
}
