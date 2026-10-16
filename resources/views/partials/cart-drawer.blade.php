<div id="cart-drawer" class="hidden fixed inset-0 z-50" role="dialog" aria-modal="true" aria-label="Shopping cart">
    <div class="absolute inset-0 bg-ink/40" data-close-cart></div>
    <aside class="absolute right-0 top-0 h-full w-full max-w-md bg-ivory flex flex-col">
        <div class="flex items-center justify-between px-5 py-4 border-b border-ink/10">
            <h2 class="font-display text-lg">Your cart ({{ $cartCount ?? 0 }})</h2>
            <button class="p-2" data-close-cart aria-label="Close cart">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M6 6l12 12M18 6L6 18"/></svg>
            </button>
        </div>
        <div class="flex-1 overflow-y-auto px-5 py-4 space-y-4">
            @forelse (($cartItems ?? collect()) as $item)
                <div class="flex gap-3">
                    <div class="w-16 h-20 bg-linen rounded overflow-hidden shrink-0">
                        @php $p = $item->product; $im = $p?->primaryImage(); $s = $im?->path; if ($s && ! str_starts_with($s,'http')) $s = asset('storage/'.$s); @endphp
                        <img src="{{ $s ?? 'https://images.unsplash.com/photo-1441986300917-64674bd600d8?w=200&q=60&auto=format&fit=crop' }}" alt="{{ $p?->title }}" class="w-full h-full object-cover" loading="lazy">
                    </div>
                    <div class="flex-1 text-sm">
                        <p class="font-medium leading-snug">{{ $item->variant?->name ?? $p?->title }}</p>
                        <p class="text-muted text-xs mt-0.5">Qty {{ $item->quantity }}</p>
                    </div>
                </div>
            @empty
                <div class="text-center py-10">
                    <p class="text-muted text-sm">Your cart is empty.</p>
                    <a href="{{ route('products.index') }}" class="inline-block mt-3 text-sm text-forest underline underline-offset-4">Browse products</a>
                </div>
            @endforelse
        </div>
        <div class="border-t border-ink/10 px-5 py-4 space-y-3">
            <div class="flex gap-3">
                <a href="{{ route('cart.index') }}" class="flex-1 text-center border border-forest text-forest text-sm font-medium px-4 py-2.5 rounded">View cart</a>
                <a href="{{ route('checkout.index') }}" class="flex-1 text-center bg-forest text-white text-sm font-medium px-4 py-2.5 rounded">Checkout</a>
            </div>
        </div>
    </aside>
</div>
