@extends('layouts.store')
@section('title', 'আপনার কার্ট — '.config('shop.name'))
@section('content')
<section class="max-w-5xl mx-auto px-4 sm:px-6 pt-8 md:pt-12">
    <div class="section-header">
        <div>
            <h1 class="section-title">আপনার কার্ট</h1>
            <p class="section-subtitle">চেকআউটের আগে আপনার আইটেমগুলো দেখে নিন।</p>
        </div>
        <a href="{{ route('products.index') }}" class="text-sm text-forest underline underline-offset-4 hidden sm:inline">কেনাকাটা চালিয়ে যান</a>
    </div>
    @if ($cart->items->isEmpty())
    <div class="empty-state mt-10 animate-fade-in">
        <div class="w-16 h-16 rounded-full bg-lined flex items-center justify-center mx-auto mb-4 text-ink/20">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <path d="M6 8h15l-1.5 8.5a1 1 0 0 1-1 .5H8.7a1 1 0 0 1-1-.8L5 4.5A1 1 0 0 0 4 4H2" />
                <circle cx="9.5" cy="20" r="1.3" />
                <circle cx="17" cy="20" r="1.3" />
            </svg>
        </div>
        <p class="empty-state-title">আপনার কার্ট খালি।</p>
        <p class="empty-state-desc">কেনাকাটা শুরু করতে পণ্য যোগ করুন।</p>
        <a href="{{ route('products.index') }}" class="mt-6 btn btn-primary btn-md">পণ্য দেখুন</a>
    </div>
    @else
    <form action="{{ route('cart.update', $cart->items->first()) }}" method="POST" class="mt-6" @csrf @method('PATCH')>
        <ul class="divide-y divide-ink/5">
            @foreach ($cart->items as $item)
            @php $p = $item->product; $price = (float) ($item->variant?->price_override ?? $p->base_price); @endphp
            <li class="py-6 flex gap-4 items-center last:border-0">
                <div class="relative w-24 h-28 bg-linen rounded-xl overflow-hidden shrink-0">
                    @php $im = $p->primaryImage(); $s = $im?->path; if ($s && ! str_starts_with($s, 'http')) $s = asset('storage/'.$s); @endphp
                    <img src="{{ $s ?? 'https://images.unsplash.com/photo-1441986300917-64674bd600d8?w=300&q=60&auto=format&fit=crop' }}" alt="{{ $p->title }}" class="w-full h-full object-cover" loading="lazy">
                    @if ($item->variant)<span class="absolute bottom-2 left-2 bg-ink text-white text-[10px] px-2 py-0.5 rounded">{{ $item->variant->name }}</span>@endif
                </div>
                <div class="flex-1 min-w-0">
                    <a href="{{ route('products.show', $p->slug) }}" class="font-medium text-ink hover:text-forest transition-colors line-clamp-1">{{ $item->variant?->name ?? $p->title }}</a>
                    <p class="text-xs text-muted mt-1">SKU {{ $p->sku }} · ৳{{ number_format($price, 0) }} প্রতিটি</p>
                    <div class="mt-3 flex items-center gap-3 qty-selector w-48">
                        <input type="hidden" name="items[{{ $item->id }}][id]" value="{{ $item->id }}">
                        <button type="button" class="qty-btn" onclick="updateQty(this, -1)">−</button>
                        <input id="q{{ $item->id }}" name="items[{{ $item->id }}][qty]" type="number" min="1" max="20" value="{{ $item->quantity }}" class="qty-input">
                        <button type="button" class="qty-btn" onclick="updateQty(this, 1)">+</button>
                        <button type="submit" class="ml-3 text-xs text-forest underline underline-offset-4 hidden sm:block">আপডেট</button>
                    </div>
                </div>
                <div class="ml-auto min-w-22.5 text-right">
                    <p class="font-semibold text-[15px]">৳{{ number_format($price * $item->quantity, 0) }}</p>
                    <form action="{{ route('cart.destroy', $item) }}" method="POST" class="mt-2" onsubmit="return confirm('এই আইটেমটি সরান?');">
                        @csrf @method('DELETE')
                        <button class="text-xs text-error underline underline-offset-4 hover:text-error">সরান</button>
                    </form>
                </div>
            </li>
            @endforeach
        </ul>
        <input type="hidden" name="_update_all" value="1">
    </form>
    <div class="mt-10 border border-ink/10 rounded-xl p-6 md:p-8 bg-white shadow-sm">
        <div class="flex justify-between items-baseline mb-4">
            <span class="text-muted text-sm">{{ $totals['count'] }}টি আইটেম{{ 1=== 1 ? '' : 's' }}</span>
            <p class="text-lg font-semibold">উপমোট <span>৳{{ number_format($totals['subtotal'], 0) }}</span></p>
        </div>
        <p class="text-xs text-muted mb-6">ডেলিভারি খরচ চেকআউটে দেখানো হবে (ঢাকার ভিতরে ৳60 / বাইরে ৳120)।</p>
        <div class="grid sm:grid-cols-2 gap-3">
            <a href="{{ route('products.index') }}" class="flex-1 text-center border border-ink/20 text-sm font-medium px-4 py-3.5 rounded-xl text-ink hover:bg-lined transition-colors">কেনাকাটা চালিয়ে যান</a>
            <a href="{{ route('checkout.index') }}" class="flex-1 text-center btn btn-cta btn-lg text-sm font-medium">চেকআউট</a>
        </div>
    </div>
    @endif
</section>
@push('scripts')
<script>
    function updateQty(btn, delta) {
        const input = btn.closest('.qty-selector').querySelector('.qty-input');
        let val = parseInt(input.value) + delta;
        if (val < 1) val = 1;
        if (val > 20) val = 20;
        input.value = val;
        btn.closest('form')?.requestSubmit();
    }
</script>
@endpush
@endsection