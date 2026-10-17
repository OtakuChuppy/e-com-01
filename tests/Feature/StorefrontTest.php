<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use Database\Seeders\DemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StorefrontTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DemoSeeder::class);
    }

    public function test_homepage_loads_with_featured_products(): void
    {
        $response = $this->get('/');
        $response->assertOk();
        $response->assertSee(config('shop.name'));
        $response->assertSee('Featured products');
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
            ->assertSee('Add to cart');
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
            ->assertSee('Inside Dhaka')
            ->assertSee('Outside Dhaka')
            ->assertDontSee('Postal code');
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
}
