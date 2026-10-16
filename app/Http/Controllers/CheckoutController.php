<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Setting;
use App\Models\StockMovement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        $cart = app(CartController::class)->cart($request);
        $cart->load(['items.product', 'items.variant']);
        $totals = app(CartController::class)->totals($cart);

        if ($cart->items->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty.');
        }

        return view('checkout.index', [
            'cart' => $cart,
            'totals' => $totals,
            'deliveryInside' => (float) (Setting::get('delivery_inside_dhaka', '60') ?? 60),
            'deliveryOutside' => (float) (Setting::get('delivery_outside_dhaka', '120') ?? 120),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'string', 'max:20', 'regex:/^[0-9+\-\s]{6,20}$/'],
            'email' => ['nullable', 'email', 'max:190'],
            'address_line1' => ['required', 'string', 'max:255'],
            'address_line2' => ['nullable', 'string', 'max:255'],
            'area' => ['nullable', 'string', 'max:120'],
            'district' => ['required', 'string', 'max:120'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'delivery_notes' => ['nullable', 'string', 'max:500'],
            'idempotency_key' => ['required', 'string', 'max:64'],
        ]);

        $cacheKey = 'order:'.$validated['idempotency_key'];
        if ($existing = cache()->get($cacheKey)) {
            return redirect()->route('checkout.success', ['reference' => $existing]);
        }

        $cart = app(CartController::class)->cart($request);
        $cart->load(['items.product', 'items.variant']);
        if ($cart->items->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty.');
        }

        try {
            $order = DB::transaction(function () use ($validated, $cart, $request) {
                return $this->placeOrder($validated, $cart, $request);
            }, 3);
        } catch (\RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        cache()->put($cacheKey, $order->reference, now()->addMinutes(10));

        return redirect()->route('checkout.success', ['reference' => $order->reference, 'token' => $order->lookup_token]);
    }

    private function placeOrder(array $v, Cart $cart, Request $request): Order
    {
        $subtotal = 0;
        $lines = [];
        foreach ($cart->items as $item) {
            $product = Product::published()->lockForUpdate()->findOrFail($item->product_id);
            $variant = $item->product_variant_id ? ProductVariant::where('product_id', $product->id)->lockForUpdate()->findOrFail($item->product_variant_id) : null;
            $price = (float) ($variant?->price_override ?? $product->base_price);
            $available = $variant?->stock_quantity ?? $product->stock_quantity;
            if ($available < $item->quantity) {
                throw new \RuntimeException("Insufficient stock for {$product->title}. Only {$available} left.");
            }
            if ($variant) {
                $variant->decrement('stock_quantity', $item->quantity);
                $balance = (int) $variant->fresh()->stock_quantity;
            } else {
                $product->decrement('stock_quantity', $item->quantity);
                $balance = (int) $product->fresh()->stock_quantity;
            }
            StockMovement::create(['product_id' => $product->id, 'product_variant_id' => $variant?->id, 'change_qty' => -$item->quantity, 'balance_after' => $balance, 'reason' => 'sale']);
            $lt = $price * $item->quantity;
            $subtotal += $lt;
            $lines[] = [$product, $variant, $price, $item->quantity, $lt];
        }
        $inside = mb_strtolower(trim($v['district'])) === 'dhaka';
        $delivery = $inside ? (float) (Setting::get('delivery_inside_dhaka', '60') ?? 60) : (float) (Setting::get('delivery_outside_dhaka', '120') ?? 120);
        $customer = Customer::firstOrCreate(['phone' => $v['phone']], ['name' => $v['name'], 'email' => $v['email'] ?? null, 'user_id' => $request->user()?->id]);
        do {
            $reference = 'DM-'.strtoupper(Str::random(6));
        } while (Order::where('reference', $reference)->exists());
        $order = Order::create(['reference' => $reference, 'lookup_token' => Str::random(48), 'customer_id' => $customer->id, 'user_id' => $request->user()?->id, 'customer_name' => $v['name'], 'customer_phone' => $v['phone'], 'customer_email' => $v['email'] ?? null, 'address_line1' => $v['address_line1'], 'address_line2' => $v['address_line2'] ?? null, 'area' => $v['area'] ?? null, 'district' => $v['district'], 'postal_code' => $v['postal_code'] ?? null, 'delivery_notes' => $v['delivery_notes'] ?? null, 'subtotal' => $subtotal, 'delivery_charge' => $delivery, 'discount' => 0, 'total' => $subtotal + $delivery, 'payment_method' => 'cod', 'payment_status' => 'unpaid', 'status' => 'pending']);
        foreach ($lines as [$p, $var, $price, $qty, $lt]) {
            OrderItem::create(['order_id' => $order->id, 'product_id' => $p->id, 'product_variant_id' => $var?->id, 'name' => $p->title, 'sku' => $var?->sku ?? $p->sku, 'variant_name' => $var?->name, 'unit_price' => $price, 'quantity' => $qty, 'line_total' => $lt]);
        }
        OrderStatusHistory::create(['order_id' => $order->id, 'from_status' => null, 'to_status' => 'pending', 'note' => 'Order placed (COD)']);
        $cart->items()->delete();

        return $order;
    }

    public function success(string $reference, Request $request): View
    {
        $order = Order::with('items')->where('reference', $reference)->firstOrFail();
        if ($request->get('token') !== $order->lookup_token && session('order_'.$order->reference) !== true) {
            abort(403, 'Order verification required.');
        }
        session(['order_'.$order->reference => true]);

        return view('checkout.success', compact('order'));
    }
}
