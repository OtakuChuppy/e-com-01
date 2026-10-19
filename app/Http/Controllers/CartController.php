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
            'buy_now' => ['nullable', 'boolean'],
        ]);

        $product = Product::published()->findOrFail($validated['product_id']);

        // Products with options must go through the detail page so the
        // customer picks a variant (quick buttons on cards can't guess it).
        $hasVariants = ProductVariant::where('product_id', $product->id)->where('is_active', true)->exists();
        if ($hasVariants && empty($validated['variant_id'])) {
            return redirect()->route('products.show', $product->slug)
                ->with('error', 'আগে একটি অপশন বাছাই করুন, তারপর এগিয়ে যান।');
        }

        $variant = null;
        if (! empty($validated['variant_id'])) {
            $variant = ProductVariant::where('product_id', $product->id)->where('is_active', true)->findOrFail($validated['variant_id']);
        }

        $available = $variant?->stock_quantity ?? $product->stock_quantity;
        if ($available < $validated['quantity']) {
            return back()->with('error', 'দুঃখিত, এই পণ্যটির মাত্র '.$available.'টি স্টক আছে।');
        }

        $cart = $this->cart($request, true);

        $item = CartItem::firstOrNew([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'product_variant_id' => $variant?->id,
        ]);
        $item->quantity = min(($item->exists ? $item->quantity : 0) + $validated['quantity'], min(20, $available));
        $item->save();

        // Cart cookie: secure flag production-e true hoy (HTTPS-only), httpOnly holo
        // true jar madhyome JavaScript theke cookie-ti chawa jay na (XSS theke rokkha).
        // SameSite=Lax cross-site request-theke CSRF jhuki komay.
        $secure = app()->environment('production');
        $cookie = cookie('cart_token', $cart->session_token, 60 * 24 * 30, '/', null, $secure, true, false, 'Lax');

        // ⚡ Order Now → skip the cart page, land straight on checkout.
        if ($request->boolean('buy_now')) {
            return redirect()->route('checkout.index')->withCookie($cookie);
        }

        return redirect()->route('cart.index')->with('success', 'কার্টে যোগ হয়েছে।')->withCookie($cookie);
    }

    public function update(Request $request, CartItem $item): RedirectResponse
    {
        $this->authorizeItem($request, $item);
        $validated = $request->validate(['quantity' => ['required', 'integer', 'min:1', 'max:20']]);
        $available = $item->variant?->stock_quantity ?? $item->product->stock_quantity;
        $item->update(['quantity' => min($validated['quantity'], max(1, (int) $available))]);

        return back()->with('success', 'কার্ট আপডেট হয়েছে।');
    }

    public function destroy(Request $request, CartItem $item): RedirectResponse
    {
        $this->authorizeItem($request, $item);
        $item->delete();

        return back()->with('success', 'আইটেম সরানো হয়েছে।');
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
            $secure = app()->environment('production');
            cookie()->queue('cart_token', $token, 60 * 24 * 30, '/', null, $secure, true, false, 'Lax');
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
