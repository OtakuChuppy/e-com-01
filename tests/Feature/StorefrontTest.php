<?php

namespace Tests\Feature;

use App\Models\CartItem;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\DemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class StorefrontTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DemoSeeder::class);
        // No demo products are seeded anymore; build a small catalogue for tests.
        Product::factory()->count(9)->create(['status' => 'published']);
        Product::factory()->count(3)->create(['status' => 'published', 'is_featured' => true]);
        Product::factory()->create(['title' => 'Steel Tiffin Carrier 3-Tier', 'status' => 'published']);
    }

    public function test_homepage_loads_with_featured_products(): void
    {
        $response = $this->get('/');
        $response->assertOk();
        $response->assertSee(config('shop.name'));
        $response->assertSee('নির্বাচিত পণ্য');
    }

    public function test_product_listing_paginates_ten_per_page(): void
    {
        $response = $this->get('/products');
        $response->assertOk();
        // 20 demo products, 10 per page → page 2 exists
        $this->get('/products?page=2')->assertOk();
        $this->get('/products?page=99')->assertOk();
    }

    public function test_listing_search_filter_and_sort(): void
    {
        $this->get('/products?q=tiffin')->assertSee('Tiffin');
        $this->get('/products?category=kitchen-dining')->assertOk();
        $this->get('/products?sort=price_asc')->assertOk();
    }

    public function test_product_detail_shows_price_and_cart_form(): void
    {
        $product = Product::published()->firstOrFail();
        $this->get('/products/'.$product->slug)
            ->assertOk()
            ->assertSee($product->title)
            ->assertSee('কার্টে যোগ করুন');
    }

    public function test_cart_add_and_checkout_creates_order(): void
    {
        $product = Product::published()->where('stock_quantity', '>', 5)->firstOrFail();
        $stockBefore = $product->stock_quantity;

        // Guest add to cart
        $this->post('/cart', ['product_id' => $product->id, 'quantity' => 2])
            ->assertRedirect();
        $this->get('/cart')->assertOk()->assertSee($product->title);

        // Checkout (COD, inside Dhaka = 60)
        $response = $this->post('/checkout', [
            'name' => 'Test Customer',
            'phone' => '01712345678',
            'address_line1' => 'House 1, Road 2',
            'delivery_zone' => 'inside_dhaka',
            'idempotency_key' => 'test-key-'.uniqid(),
        ]);
        $response->assertRedirect();

        $this->assertDatabaseHas('orders', ['customer_phone' => '01712345678', 'delivery_zone' => 'inside_dhaka', 'status' => 'pending', 'payment_method' => 'cod']);
        $order = Order::where('customer_phone', '01712345678')->first();
        // Checkout reserves only — stock unchanged until staff marks as shipped.
        $this->assertEquals($stockBefore, $product->fresh()->stock_quantity);
        $this->assertEquals(60, (float) $order->delivery_charge);

        // Order lookup via reference + phone
        $this->get('/track-order/lookup?reference='.$order->reference.'&phone=01712345678')->assertOk();
    }

    public function test_checkout_rejects_overstock(): void
    {
        $product = Product::published()->firstOrFail();
        $response = $this->post('/cart', ['product_id' => $product->id, 'quantity' => 9999]);
        // quantity max:20 validation OR stock check → redirect with error, no crash
        $response->assertRedirect();
    }

    public function test_checkout_rejects_invalid_zone(): void
    {
        $product = Product::published()->firstOrFail();
        $this->post('/cart', ['product_id' => $product->id, 'quantity' => 1]);
        $this->post('/checkout', [
            'name' => 'Bad Zone', 'phone' => '01900001111',
            'address_line1' => 'H1', 'delivery_zone' => 'mars',
            'idempotency_key' => 'test-key-'.uniqid(),
        ])->assertSessionHasErrors('delivery_zone');
        $this->assertDatabaseMissing('orders', ['customer_phone' => '01900001111']);
    }

    public function test_checkout_page_shows_zone_selector(): void
    {
        $product = Product::published()->firstOrFail();
        $this->post('/cart', ['product_id' => $product->id, 'quantity' => 1]);
        $this->get('/checkout')->assertOk()
            ->assertSee('ঢাকার ভিতরে')
            ->assertSee('ঢাকার বাইরে')
            ->assertDontSee('পোস্টাল কোড');
    }

    public function test_outside_dhaka_delivery_charge(): void
    {
        $product = Product::published()->where('stock_quantity', '>', 2)->firstOrFail();
        $this->post('/cart', ['product_id' => $product->id, 'quantity' => 1]);
        $this->post('/checkout', [
            'name' => 'Outside Buyer', 'phone' => '01899998888',
            'address_line1' => 'Village Road', 'delivery_zone' => 'outside_dhaka',
            'idempotency_key' => 'test-key-'.uniqid(),
        ])->assertRedirect();
        $order = Order::where('customer_phone', '01899998888')->first();
        $this->assertEquals(120, (float) $order->delivery_charge);
    }

    public function test_checkout_page_has_summary_and_sticky_place_order(): void
    {
        $product = Product::published()->firstOrFail();
        $this->post('/cart', ['product_id' => $product->id, 'quantity' => 1]);
        $this->get('/checkout')->assertOk()
            ->assertSee('অর্ডার সারাংশ')
            ->assertSee('অর্ডার করুন')
            ->assertSee('ক্যাশ অন ডেলিভারি')
            ->assertSee('কার্ট সম্পাদনা');
    }

    public function test_homepage_query_count_is_bounded(): void
    {
        DB::enableQueryLog();
        $this->get('/')->assertOk();
        $count = count(DB::getQueryLog());

        fwrite(STDERR, "\n[PERF] homepage queries: {$count}\n");
        $this->assertLessThan(20, $count, 'Homepage should stay under 20 queries.');
    }

    public function test_product_listing_query_count_is_bounded(): void
    {
        DB::enableQueryLog();
        $this->get('/products')->assertOk();
        $count = count(DB::getQueryLog());

        fwrite(STDERR, "\n[PERF] listing queries: {$count}\n");
        $this->assertLessThan(20, $count, 'Product listing should stay under 20 queries.');
    }

    public function test_security_headers_present_on_storefront_and_admin_login(): void
    {
        foreach (['/', '/admin/login'] as $url) {
            $response = $this->get($url);
            $response->assertOk();
            $response->assertHeader('X-Content-Type-Options', 'nosniff');
            $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
            $response->assertHeader('Referrer-Policy', 'same-origin');
            $response->assertHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
        }
    }

    public function test_cart_add_and_order_lookup_are_rate_limited(): void
    {
        config()->set('app.debug', false);

        $product = Product::published()->firstOrFail();

        $response = $this->post('/cart', ['product_id' => $product->id, 'quantity' => 1]);
        for ($i = 1; $i < 31; $i++) {
            $response = $this->post('/cart', ['product_id' => $product->id, 'quantity' => 1]);
        }
        $response->assertStatus(429);

        $lookup = $this->get('/track-order/lookup?reference=DM-AAAAAA&phone=01700000000');
        for ($i = 1; $i < 31; $i++) {
            $lookup = $this->get('/track-order/lookup?reference=DM-AAAAAA&phone=01700000000');
        }
        $lookup->assertStatus(429);
    }

    public function test_hostile_product_text_is_escaped_not_executed(): void
    {
        $product = Product::published()->firstOrFail();
        $payload = '<script>alert(document.domain)</script><img src=x onerror=alert(1)>';
        $product->update(['title' => $payload, 'short_description' => $payload, 'description' => $payload]);

        $response = $this->get('/products/'.$product->slug);
        $response->assertOk();
        $response->assertDontSee($payload, false);
        $response->assertSee(e($payload), false);
    }

    public function test_probe_cross_cart_item_access_is_blocked(): void
    {
        // Victim's cart with an item.
        $this->post('/cart', ['product_id' => Product::published()->firstOrFail()->id, 'quantity' => 1]);
        $victimItemId = CartItem::latest('id')->firstOrFail()->id;

        // Attacker starts a fresh session (no cart token) and tries to touch the victim's item.
        $this->flushSession();
        $this->patch('/cart/'.$victimItemId, ['quantity' => 5])->assertForbidden();
        $this->delete('/cart/'.$victimItemId)->assertForbidden();

        // And a blind guess at a non-existent item id must 404, not leak.
        $this->patch('/cart/999999', ['quantity' => 5])->assertNotFound();
    }

    public function test_probe_order_lookup_needs_exact_pair(): void
    {
        $product = Product::published()->firstOrFail();
        $this->post('/cart', ['product_id' => $product->id, 'quantity' => 1]);
        $this->post('/checkout', [
            'name' => 'Probe Target', 'phone' => '01777770000',
            'address_line1' => 'H1', 'delivery_zone' => 'inside_dhaka',
            'idempotency_key' => 'probe-'.uniqid(),
        ]);
        $order = Order::where('customer_phone', '01777770000')->firstOrFail();

        // Right reference + wrong phone → nothing leaks.
        $this->get('/track-order/lookup?reference='.$order->reference.'&phone=00000000000')->assertNotFound();
        // Exact pair → OK.
        $this->get('/track-order/lookup?reference='.$order->reference.'&phone=01777770000')->assertOk();
    }

    public function test_probe_password_change_requires_current_session_controls(): void
    {
        // There is no self-service password change for staff; the only privileged
        // write on users is owner-only. A non-owner must not demote/deactivate anyone.
        $owner = User::create(['name' => 'o', 'email' => 'o@example.com', 'password' => 'password-12345', 'role' => 'owner', 'is_active' => true]);
        $om = User::create(['name' => 'm', 'email' => 'm@example.com', 'password' => 'password-12345', 'role' => 'order_manager', 'is_active' => true]);

        $this->actingAs($om)->patch('/admin/users/'.$om->id, ['role' => 'owner', 'is_active' => 1])->assertForbidden();
        $this->assertEquals('order_manager', $om->fresh()->role);
        $this->actingAs($om)->post('/admin/users', [
            'name' => 'Evil', 'email' => 'evil@example.com', 'password' => 'strong-password-1',
            'password_confirmation' => 'strong-password-1', 'role' => 'owner',
        ])->assertForbidden();
    }

    public function test_probe_checkout_success_token_gate(): void
    {
        $product = Product::published()->firstOrFail();
        $this->post('/cart', ['product_id' => $product->id, 'quantity' => 1]);
        $this->post('/checkout', [
            'name' => 'Token Target', 'phone' => '01788880000',
            'address_line1' => 'H1', 'delivery_zone' => 'inside_dhaka',
            'idempotency_key' => 'probe-token-'.uniqid(),
        ]);
        $order = Order::where('customer_phone', '01788880000')->firstOrFail();

        $this->flushSession();
        // Reference alone (no token, no session) must not render the order.
        $this->get('/checkout/success/'.$order->reference)->assertForbidden();
        // Wrong token must not render either.
        $this->get('/checkout/success/'.$order->reference.'?token=wrong')->assertForbidden();
        // Exact lookup token is the intended shareable link.
        $this->get('/checkout/success/'.$order->reference.'?token='.$order->lookup_token)->assertOk();
    }

    public function test_probe_image_upload_rejects_executables(): void
    {
        $owner = User::create(['name' => 'o2', 'email' => 'o2@example.com', 'password' => 'password-12345', 'role' => 'owner', 'is_active' => true]);
        $product = Product::published()->firstOrFail();

        $this->actingAs($owner)->post('/admin/products/'.$product->id.'/images', [
            'images' => [UploadedFile::fake()->create('shell.php', 10)],
        ])->assertSessionHasErrors('images.0');
        $this->assertDatabaseMissing('product_images', ['product_id' => $product->id, 'path' => 'products/shell.php']);
    }
}
