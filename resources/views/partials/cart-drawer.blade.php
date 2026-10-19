<div id="cart-drawer" class="hidden fixed inset-0 z-50" role="dialog" aria-modal="true" aria-label="শপিং কার্ট">
    <div class="absolute inset-0 bg-ink/40 backdrop-blur-sm" data-close-cart></div>
    <aside class="absolute right-0 top-0 h-full w-full max-w-md bg-white border-l border-ink/10 flex flex-col shadow-2xl">
        <div class="flex items-center justify-between px-6 py-5 border-b border-ink/10 bg-sand">
            <h2 class="font-display text-lg font-semibold">আপনার কার্ট</h2>
            <button class="p-2 text-ink hover:text-forest transition-colors" data-close-cart aria-label="কার্ট বন্ধ করুন">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M6 6l12 12M18 6L6 18"/></svg>
            </button>
        </div>
        <div class="flex-1 overflow-y-auto px-6 py-5">
            @forelse (($cartItems ?? collect()) as $item)
                @php $p = $item->product; $price = (float) ($item->variant?->price_override ?? $p->base_price); @endphp
                <div class="flex gap-4 items-start mb-5 last:mb-0 pb-4 border-b border-ink/5 last:border-0">
                    <div class="relative w-18 h-22 bg-linen rounded-lg overflow-hidden shrink-0">
                        @php $im = $p?->primaryImage(); $s = $im?->path; if ($s && ! str_starts_with($s,'http')) $s = asset('storage/'.$s); @endphp
                        <img src="{{ $s ?? 'https://images.unsplash.com/photo-1441986300917-64674bd600d8?w=200&q=60&auto=format&fit=crop' }}" alt="{{ $p?->title }}" class="w-full h-full object-cover" loading="lazy">
                    </div>
                    <div class="flex-1 min-w-0">
                        <a href="{{ route('products.show', $p->slug) }}" class="font-medium text-ink hover:text-forest transition-colors line-clamp-1">{{ $item->variant?->name ?? $p->title }}</a>
                        @if ($item->variant)<p class="text-xs text-muted mt-1">{{ $item->variant->name }}</p>@endif
                        <div class="mt-3 flex items-center gap-3">
                            <form action="{{ route('cart.update', $item) }}" method="POST" class="flex items-center gap-2 qty-selector" onsubmit="return false;">
                                @csrf @method('PATCH')
                                <button type="button" class="qty-btn" onclick="updateQty(this, -1)">−</button>
                                <input type="hidden" name="quantity" value="{{ $item->quantity }}" class="qty-val">
                                <input id="q{{ $item->id }}" name="quantity" type="number" min="1" max="20" value="{{ $item->quantity }}" class="qty-input">
                                <button type="button" class="qty-btn" onclick="updateQty(this, 1)">+</button>
                                <button type="submit" class="text-xs text-forest underline underline-offset-4 ml-2 hidden sm:block">আপডেট</button>
                            </form>
                            <form action="{{ route('cart.destroy', $item) }}" method="POST" class="ml-2" onsubmit="return confirm('এই আইটেমটি সরান?');">
                                @csrf @method('DELETE')
                                <button class="text-xs text-error underline underline-offset-4 hover:text-error">সরান</button>
                            </form>
                        </div>
                        <p class="mt-2 font-semibold text-[15px]">৳{{ number_format($price * $item->quantity, 0) }}</p>
                    </div>
                </div>
            @empty
                <div class="text-center py-16">
                    <div class="w-12 h-12 rounded-full bg-lined flex items-center justify-center mx-auto mb-3 text-ink/30">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M6 8h15l-1.5 8.5a1 1 0 0 1-1 .5H8.7a1 1 0 0 1-1-.8L5 4.5A1 1 0 0 0 4 4H2"/><circle cx="9.5" cy="20" r="1.3"/><circle cx="17" cy="20" r="1.3"/></svg>
                    </div>
                    <p class="text-muted text-sm">আপনার কার্ট খালি।</p>
                    <a href="{{ route('products.index') }}" class="inline-block mt-4 btn btn-primary btn-md">পণ্য দেখুন</a>
                </div>
            @endforelse
        </div>
        <div class="border-t border-ink/10 px-6 py-5 space-y-3 bg-white">
            @php $drawerSubtotal = collect($cartItems ?? [])->sum(fn($i) => (float)($i->variant?->price_override ?? $i->product->base_price) * $i->quantity); @endphp
            <div class="flex justify-between items-baseline">
                <span class="text-muted text-sm">উপমোট</span>
                <span class="font-semibold text-lg" id="cart-subtotal">৳{{ number_format($drawerSubtotal, 0) }}</span>
            </div>
            <p class="text-xs text-muted text-center">ডেলিভারি খরচ চেকআউটে দেখানো হবে (ঢাকার ভিতরে ৳60 / বাইরে ৳120)।</p>
            <div class="flex gap-3">
                <a href="{{ route('cart.index') }}" class="flex-1 text-center border border-ink/20 text-sm font-medium px-4 py-3 rounded-md text-ink hover:bg-lined transition-colors">কার্ট দেখুন</a>
                <a href="{{ route('checkout.index') }}" class="flex-1 text-center btn btn-cta btn-md text-sm font-medium">চেকআউট</a>
            </div>
        </div>
    </aside>
</div>
<script>
function updateQty(btn, delta) {
    const form = btn.closest('form');
    const input = form.querySelector('.qty-input');
    let val = parseInt(input.value) + delta;
    if (val < 1) val = 1;
    if (val > 20) val = 20;
    input.value = val;
    form.querySelector('.qty-val').value = val;
    form.requestSubmit();
}
document.addEventListener('click', (e) => {
    const open = e.target.closest('[data-open-cart]');
    const close = e.target.closest('[data-close-cart]');
    const drawer = document.getElementById('cart-drawer');
    if (!drawer) return;
    if (open) { drawer.classList.remove('hidden'); document.body.style.overflow = 'hidden'; }
    if (close || e.target === drawer) { drawer.classList.add('hidden'); document.body.style.overflow = ''; }
});
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        const drawer = document.getElementById('cart-drawer');
        if (drawer) { drawer.classList.add('hidden'); document.body.style.overflow = ''; }
    }
});
</script>
