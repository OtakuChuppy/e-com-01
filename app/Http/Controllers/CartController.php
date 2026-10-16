<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CartController extends Controller
{
    public function index(Request $request): View
    {
        $cart = $this->cart($request);
        $cart->load(['items.product.images', 'items.variant']);

        return view('cart.index', ['cart' => $cart, 'totals' => $this->totals($cart)]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'variant_id' => ['nullable', 'exists:product_variants,id'],
            'quantity' => ['required', 'integer', 'min:1', 'max:20'],
        ]);

        $product = Product::published()->findOrFail($validated['product_id']);
        $variant = null;
        if (! empty($validated['variant_id'])) {
            $variant = ProductVariant::where('product_id', $product->id)->where('is_active', true)->findOrFail($validated['variant_id']);
        }

        $available = $variant?->stock_quantity ?? $product->stock_quantity;
        if ($available < $validated['quantity']) {
            return back()->with('error', 'Sorry, only '.$available.' in stock for this item.');
        }

        $cart = $this->cart($request, true);

        $item = CartItem::firstOrNew([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'product_variant_id' => $variant?->id,
        ]);
        $item->quantity = min(($item->exists ? $item->quantity : 0) + $validated['quantity'], min(20, $available));
        $item->save();

        return redirect()->route('cart.index')->with('success', 'Added to cart.')->withCookie(
            cookie('cart_token', $cart->session_token, 60 * 24 * 30, '/', null, false, true, false, 'Lax')
        );
    }

    public function update(Request $request, CartItem $item): RedirectResponse
    {
        $this->authorizeItem($request, $item);
        $validated = $request->validate(['quantity' => ['required', 'integer', 'min:1', 'max:20']]);
        $available = $item->variant?->stock_quantity ?? $item->product->stock_quantity;
        $item->update(['quantity' => min($validated['quantity'], max(1, (int) $available))]);

        return back()->with('success', 'Cart updated.');
    }

    public function destroy(Request $request, CartItem $item): RedirectResponse
    {
        $this->authorizeItem($request, $item);
        $item->delete();

        return back()->with('success', 'Item removed.');
    }

    public function cart(Request $request, bool $create = false): Cart
    {
        // Session-backed cart identity (works in browser + tests; cookie set on add-to-cart response too)
        $token = $request->cookie('cart_token') ?: $request->session()->get('cart_token');
        $cart = $token ? Cart::where('session_token', $token)->first() : null;

        if (! $cart && $create) {
            $token = Str::random(48);
            $cart = Cart::create(['session_token' => $token, 'user_id' => $request->user()?->id]);
            $request->session()->put('cart_token', $token);
            cookie()->queue('cart_token', $token, 60 * 24 * 30, '/', null, false, true, false, 'Lax');
            $request->cookies->set('cart_token', $token);
        }

        return $cart ?? new Cart;
    }

    /** @return array{subtotal: float, count: int} */
    public function totals(Cart $cart): array
    {
        $subtotal = 0;
        $count = 0;
        foreach ($cart->items as $item) {
            $price = (float) ($item->variant?->price_override ?? $item->product->base_price);
            $subtotal += $price * $item->quantity;
            $count += $item->quantity;
        }

        return ['subtotal' => $subtotal, 'count' => $count];
    }

    private function authorizeItem(Request $request, CartItem $item): void
    {
        $token = $request->cookie('cart_token') ?: $request->session()->get('cart_token');
        abort_unless($token && $item->cart->session_token === $token, 403);
    }
}
