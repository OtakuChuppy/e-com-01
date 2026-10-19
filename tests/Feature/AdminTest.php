<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\User;
use Database\Seeders\DemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Testing\File;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DemoSeeder::class);
        // No demo products are seeded anymore; build one published product for
        // order tests. Image tests build their own product + image below.
        $this->seed(DemoSeeder::class);
        Product::factory()->create(['status' => 'published']);
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

        return Order::where('customer_phone', '01711111111')->latest('id')->firstOrFail();
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

    public function test_owner_can_upload_and_manage_product_images(): void
    {
        Storage::fake('public');
        $owner = $this->makeUser('owner');
        $product = Product::create(['sku' => 'IMG-TEST-1', 'title' => 'Image Test Product',
            'status' => 'published', 'base_price' => 100, 'stock_quantity' => 5, 'low_stock_threshold' => 5, 'published_at' => now()]);

        $response = $this->actingAs($owner)->post('/admin/products/'.$product->id.'/images', [
            'images' => [UploadedFile::fake()->image('tiffin.jpg'),
                UploadedFile::fake()->image('tiffin-2.jpg')],
            'alt_text' => 'Steel tiffin',
        ]);
        $response->assertRedirect();
        $this->assertDatabaseCount('product_images', 2); // 2 new images, no demo images remain
        $images = $product->images()->get();
        $first = $images->first();
        $this->assertTrue((bool) $first->is_primary); // first upload on imageless product is primary
        $this->assertSame('Steel tiffin', $first->alt_text);

        // Set the second image as primary, then delete it — primary falls back
        $second = $images->last();
        $this->actingAs($owner)->patch('/admin/products/'.$product->id.'/images/'.$second->id.'/primary')->assertRedirect();
        $this->assertTrue((bool) $second->fresh()->is_primary);
        $this->assertFalse((bool) $first->fresh()->is_primary);

        $this->actingAs($owner)->delete('/admin/products/'.$product->id.'/images/'.$second->id)->assertRedirect();
        $this->assertDatabaseMissing('product_images', ['id' => $second->id]);
        $this->assertTrue((bool) $first->fresh()->is_primary); // fallback to remaining image
    }

    public function test_image_upload_rejects_non_images_and_cross_product_access(): void
    {
        Storage::fake('public');
        $owner = $this->makeUser('owner');
        $product = Product::factory()->create(['status' => 'published']);
        ProductImage::create(['product_id' => $product->id, 'path' => 'https://example.com/victim.jpg',
            'alt_text' => 'victim', 'sort_order' => 0, 'is_primary' => true]);
        $other = Product::factory()->create(['status' => 'published']);

        // Non-image file rejected
        $this->actingAs($owner)->post('/admin/products/'.$product->id.'/images', [
            'images' => [UploadedFile::fake()->create('evil.php', 10)],
        ])->assertSessionHasErrors('images.0');

        // Image belonging to another product → 404, not deleted
        $victim = $product->images()->firstOrFail();
        $this->actingAs($owner)
            ->delete('/admin/products/'.$other->id.'/images/'.$victim->id)
            ->assertNotFound();
        $this->assertDatabaseHas('product_images', ['id' => $victim->id]);
    }

    public function test_order_manager_cannot_manage_product_images(): void
    {
        $staff = $this->makeUser('order_manager');
        $product = Product::factory()->create(['status' => 'published']);
        $image = ProductImage::create(['product_id' => $product->id, 'path' => 'https://example.com/i.jpg',
            'alt_text' => 'x', 'sort_order' => 0, 'is_primary' => true]);

        $this->actingAs($staff)->post('/admin/products/'.$product->id.'/images', ['images' => ['x']])->assertForbidden();
        $this->actingAs($staff)->delete('/admin/products/'.$product->id.'/images/'.$image->id)->assertForbidden();
        $this->actingAs($staff)->patch('/admin/products/'.$product->id.'/images/'.$image->id.'/primary')->assertForbidden();
    }

    public function test_owner_can_create_staff_account(): void
    {
        $owner = $this->makeUser('owner');

        $this->actingAs($owner)->post('/admin/users', [
            'name' => 'Rahim Uddin',
            'email' => 'rahim@example.com',
            'password' => 'strong-password-1',
            'password_confirmation' => 'strong-password-1',
            'role' => 'order_manager',
            'phone' => '01711111111',
        ])->assertRedirect();

        $this->assertDatabaseHas('users', ['email' => 'rahim@example.com', 'role' => 'order_manager', 'is_active' => true]);

        // Password policy: minimum 10 characters enforced server-side
        $this->actingAs($owner)->post('/admin/users', [
            'name' => 'Short Pass',
            'email' => 'short@example.com',
            'password' => 'short1',
            'password_confirmation' => 'short1',
            'role' => 'order_manager',
        ])->assertSessionHasErrors('password');
        $this->assertDatabaseMissing('users', ['email' => 'short@example.com']);
    }

    public function test_owner_can_delete_order_with_items_and_history(): void
    {
        $owner = $this->makeUser('owner');
        $order = $this->placeOrder();
        $itemId = $order->items()->firstOrFail()->id;

        $this->actingAs($owner)->delete('/admin/orders/'.$order->id)->assertRedirect('/admin/orders');

        $this->assertDatabaseMissing('orders', ['id' => $order->id]);
        $this->assertDatabaseMissing('order_items', ['id' => $itemId]);
        $this->assertDatabaseHas('audit_logs', ['action' => 'order.delete', 'auditable_id' => $order->id]);
    }

    public function test_deleting_shipped_order_restores_inventory(): void
    {
        $owner = $this->makeUser('owner');
        $order = $this->placeOrder();
        $productId = $order->items->first()->product_id;
        $qty = (int) $order->items->first()->quantity;
        $stockBefore = Product::findOrFail($productId)->stock_quantity;

        // pending → confirmed → shipped (deducts stock)
        $this->actingAs($owner)->patch('/admin/orders/'.$order->id.'/status', ['status' => 'confirmed'])->assertRedirect();
        $this->actingAs($owner)->patch('/admin/orders/'.$order->id.'/status', ['status' => 'shipped'])->assertRedirect();
        $this->assertEquals($stockBefore - $qty, Product::findOrFail($productId)->stock_quantity);

        $this->actingAs($owner)->delete('/admin/orders/'.$order->id)->assertRedirect('/admin/orders');

        $this->assertDatabaseMissing('orders', ['id' => $order->id]);
        $this->assertEquals($stockBefore, Product::findOrFail($productId)->stock_quantity);
        $this->assertDatabaseHas('stock_movements', ['product_id' => $productId, 'change_qty' => $qty, 'reason' => 'cancelled']);
    }

    public function test_order_manager_cannot_delete_orders(): void
    {
        $staff = $this->makeUser('order_manager');
        $order = $this->placeOrder();

        $this->actingAs($staff)->delete('/admin/orders/'.$order->id)->assertForbidden();
        $this->assertDatabaseHas('orders', ['id' => $order->id]);
    }

    public function test_guests_cannot_delete_orders(): void
    {
        $order = $this->placeOrder();

        $this->delete('/admin/orders/'.$order->id)->assertRedirect('/admin/login');
        $this->assertDatabaseHas('orders', ['id' => $order->id]);
    }

    public function test_orders_index_offers_quick_status_dropdown_with_valid_transitions_only(): void
    {
        $staff = $this->makeUser('order_manager');
        $order = $this->placeOrder(); // placed as pending

        $response = $this->actingAs($staff)->get('/admin/orders?status=pending');
        $response->assertOk();
        // Pending orders offer only valid next steps in the inline dropdown…
        $response->assertSee('value="confirmed"', false);
        $response->assertSee('value="on_hold"', false);
        $response->assertSee('value="cancelled"', false);
        // …and the row's own dropdown never offers an invalid jump like pending → delivered.
        $this->assertMatchesRegularExpression('/<select id="status-'.$order->id.'".*?<\/select>/s', $response->getContent());
        preg_match('/<select id="status-'.$order->id.'".*?<\/select>/s', $response->getContent(), $m);
        $this->assertStringNotContainsString('value="delivered"', $m[0]);

        // The dropdown posts to the same guarded endpoint.
        $response->assertSee(route('admin.orders.status', $order), false);
    }

    public function test_bulk_status_updates_valid_and_skips_invalid_transitions(): void
    {
        $owner = $this->makeUser('owner');
        $first = $this->placeOrder();
        $second = $this->placeOrder();

        // Both pending → confirmed applies cleanly.
        $this->actingAs($owner)->patch('/admin/orders/bulk/status', [
            'order_ids' => [$first->id, $second->id],
            'status' => 'confirmed',
        ])->assertRedirect();
        $this->assertEquals('confirmed', $first->fresh()->status);
        $this->assertEquals('confirmed', $second->fresh()->status);
        $this->assertDatabaseHas('order_status_history', ['order_id' => $first->id, 'to_status' => 'confirmed']);
        $this->assertDatabaseHas('order_status_history', ['order_id' => $second->id, 'to_status' => 'confirmed']);

        // confirmed → delivered skips the chain: both skipped, nothing changes.
        $response = $this->actingAs($owner)->patch('/admin/orders/bulk/status', [
            'order_ids' => [$first->id, $second->id],
            'status' => 'delivered',
        ]);
        $response->assertRedirect();
        $this->assertEquals('confirmed', $first->fresh()->status);
        $this->assertEquals('confirmed', $second->fresh()->status);
        $this->assertStringContainsString('2 skipped', (string) session('success'));
    }

    public function test_bulk_status_requires_selection_and_valid_ids(): void
    {
        $owner = $this->makeUser('owner');

        $this->actingAs($owner)->patch('/admin/orders/bulk/status', ['status' => 'confirmed'])
            ->assertSessionHasErrors('order_ids');

        $this->actingAs($owner)->patch('/admin/orders/bulk/status', ['order_ids' => [999999], 'status' => 'confirmed'])
            ->assertSessionHasErrors('order_ids.0');
    }

    public function test_bulk_delete_removes_orders_and_restores_shipped_inventory(): void
    {
        $owner = $this->makeUser('owner');
        $shipped = $this->placeOrder();
        $pending = $this->placeOrder();
        $productId = $shipped->items->first()->product_id;
        $qty = (int) $shipped->items->first()->quantity;
        $stockBefore = Product::findOrFail($productId)->stock_quantity;

        $this->actingAs($owner)->patch('/admin/orders/'.$shipped->id.'/status', ['status' => 'confirmed'])->assertRedirect();
        $this->actingAs($owner)->patch('/admin/orders/'.$shipped->id.'/status', ['status' => 'shipped'])->assertRedirect();
        $this->assertEquals($stockBefore - $qty, Product::findOrFail($productId)->stock_quantity);

        $this->actingAs($owner)->delete('/admin/orders/bulk', ['order_ids' => [$shipped->id, $pending->id]])
            ->assertRedirect('/admin/orders');

        $this->assertDatabaseMissing('orders', ['id' => $shipped->id]);
        $this->assertDatabaseMissing('orders', ['id' => $pending->id]);
        $this->assertEquals($stockBefore, Product::findOrFail($productId)->stock_quantity);
        $this->assertDatabaseHas('stock_movements', ['product_id' => $productId, 'change_qty' => $qty, 'reason' => 'cancelled']);
        $this->assertDatabaseHas('audit_logs', ['action' => 'order.delete', 'auditable_id' => $shipped->id]);
        $this->assertDatabaseHas('audit_logs', ['action' => 'order.delete', 'auditable_id' => $pending->id]);
    }

    public function test_owner_can_adjust_stock_without_variant_id(): void
    {
        // Regression: the form omits variant_id entirely, so validated() has no
        // such key — the controller must not read it directly.
        $owner = $this->makeUser('owner');
        $product = Product::factory()->create(['stock_quantity' => 10, 'status' => 'published']);

        $this->actingAs($owner)->post('/admin/inventory/adjust', [
            'product_id' => $product->id,
            'change_qty' => 7,
            'reason' => 'purchase',
        ])->assertRedirect();

        $this->assertEquals(17, $product->fresh()->stock_quantity);
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'product_variant_id' => null,
            'change_qty' => 7,
            'balance_after' => 17,
            'reason' => 'purchase',
        ]);
        $this->assertDatabaseHas('audit_logs', ['action' => 'inventory.adjust', 'auditable_id' => $product->id]);
    }

    public function test_owner_can_adjust_variant_stock_and_cannot_go_negative(): void
    {
        $owner = $this->makeUser('owner');
        $product = Product::factory()->create(['stock_quantity' => 3, 'status' => 'published']);
        $variant = $product->variants()->create([
            'name' => 'Large', 'sku' => 'SKU-VAR-1', 'stock_quantity' => 5, 'is_active' => true,
        ]);

        $this->actingAs($owner)->post('/admin/inventory/adjust', [
            'product_id' => $product->id,
            'variant_id' => $variant->id,
            'change_qty' => -2,
            'reason' => 'adjustment',
        ])->assertRedirect();
        $this->assertEquals(3, $variant->fresh()->stock_quantity);
        $this->assertEquals(3, $product->fresh()->stock_quantity);

        // Adjusting below zero is refused inside the transaction.
        $this->actingAs($owner)->post('/admin/inventory/adjust', [
            'product_id' => $product->id,
            'variant_id' => $variant->id,
            'change_qty' => -10,
            'reason' => 'adjustment',
        ]);
        $this->assertEquals(3, $variant->fresh()->stock_quantity);
    }

    public function test_bulk_delete_forbidden_for_order_manager_and_guests(): void
    {
        $order = $this->placeOrder();

        $this->delete('/admin/orders/bulk', ['order_ids' => [$order->id]])->assertRedirect('/admin/login');
        $this->assertDatabaseHas('orders', ['id' => $order->id]);

        $staff = $this->makeUser('order_manager');
        $this->actingAs($staff)->delete('/admin/orders/bulk', ['order_ids' => [$order->id]])->assertForbidden();
        $this->assertDatabaseHas('orders', ['id' => $order->id]);
    }
}
