@extends('layouts.store')
@section('title', $product->meta_title ?: $product->title.' — '.config('shop.name'))
@section('meta_description', $product->meta_description ?: \Illuminate\Support\Str::limit($product->short_description ?? $product->description ?? '', 160))
@section('content')
<section class="max-w-7xl mx-auto px-4 sm:px-6 pt-8 md:pt-12">
    <p class="text-xs text-muted"><a href="{{ route('home') }}" class="hover:text-forest transition-colors">হোম</a> / <a href="{{ route('products.index') }}" class="hover:text-forest transition-colors">কেনাকাটা</a> / {{ $product->title }}</p>
    <div class="grid md:grid-cols-2 gap-8 md:gap-12 mt-4">
        <div>
            @php $imgs = $product->images; $first = $imgs->first()?->path; if ($first && ! str_starts_with($first, 'http')) $first = asset('storage/'.$first); @endphp
            <div class="rounded-2xl bg-linen overflow-hidden border border-ink/5 aspect-4/5">
                <img id="main-img" src="{{ $first ?? 'https://images.unsplash.com/photo-1441986300917-64674bd600d8?w=800&q=80&auto=format&fit=crop' }}" alt="{{ $product->title }}" class="w-full h-full object-cover" width="800" height="1000" fetchpriority="high" onerror="this.onerror=null;this.src='https://images.unsplash.com/photo-1441986300917-64674bd600d8?w=800&q=80&auto=format&fit=crop';">
            </div>
            @if ($imgs->count() > 1)
            <div class="grid grid-cols-5 gap-2 mt-3">
                @foreach ($imgs as $im)
                @php $s = $im->path; if (! str_starts_with($s, 'http')) $s = asset('storage/'.$s); @endphp
                <button data-thumb="{{ $s }}" class="rounded-lg bg-linen aspect-square overflow-hidden border border-transparent hover:border-forest transition-colors focus-visible:ring-2 focus-visible:ring-forest" aria-label="ছবি দেখুন"><img src="{{ $s }}" alt="{{ $im->alt_text ?? $product->title }}" class="w-full h-full object-cover" loading="lazy"></button>
                @endforeach
            </div>
            @endif
        </div>
        <div>
            <p class="text-xs text-muted uppercase tracking-wider">{{ $product->category?->name }} @if($product->brand)· {{ $product->brand }}@endif</p>
            <h1 class="font-display text-3xl md:text-4xl tracking-tight mt-1">{{ $product->title }}</h1>
            <div class="mt-3 flex items-baseline gap-3">
                <span class="price text-2xl">৳{{ number_format((float) $product->base_price, 0) }}</span>
                @if ($product->compare_at_price && (float) $product->compare_at_price > (float) $product->base_price)
                    <span class="price-compare text-lg">৳{{ number_format((float) $product->compare_at_price, 0) }}</span>
                    <span class="price-save">{{ $product->discount_percent }}% ছাড়</span>
                @endif
            </div>
            <p class="text-sm mt-2">@if($product->in_stock)<span class="badge badge-forest">স্টক আছে</span>@else<span class="badge" style="background:#fbeae8;color:#b3402e;">স্টক নেই</span>@endif <span class="text-muted">· SKU {{ $product->sku }}</span></p>
            @if ($product->short_description)<p class="text-[15px] text-ink/80 leading-relaxed mt-4">{{ $product->short_description }}</p>@endif

            {{-- Two action buttons: Add to Cart (cart) + Order Now (checkout) --}}
            @if ($product->in_stock)
            <div class="mt-6 space-y-3">
                @if ($product->variants->where('is_active', true)->count())
                    <form action="{{ route('cart.store') }}" method="POST" class="space-y-3">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                        <div class="field">
                            <label for="variant_id" class="text-sm font-medium">অপশন</label>
                            <select id="variant_id" name="variant_id" class="input">
                                @foreach ($product->variants->where('is_active', true) as $v)
                                    <option value="{{ $v->id }}">{{ $v->name }} · ৳{{ number_format((float) ($v->price_override ?? $product->base_price), 0) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex items-center gap-3">
                            <label class="sr-only" for="qty">পরিমাণ</label>
                            <div class="qty-selector w-32">
                                <button type="button" class="qty-btn" onclick="updateQty(this, -1)">−</button>
                                <input type="hidden" name="quantity" value="1" class="qty-val">
                                <input id="qty" name="quantity" type="number" min="1" max="20" value="1" class="qty-input">
                                <button type="button" class="qty-btn" onclick="updateQty(this, 1)">+</button>
                            </div>
                            <button class="flex-1 btn btn-primary btn-md">কার্টে যোগ করুন</button>
                        </div>
                    </form>
                    <form action="{{ route('cart.store') }}" method="POST" class="flex w-full">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                        <input type="hidden" name="quantity" value="1">
                        <input type="hidden" name="buy_now" value="1">
                        <button class="flex-1 btn btn-cta btn-md text-sm font-medium">⚡ অর্ডার করুন</button>
                    </form>
                @else
                    <div class="flex flex-col sm:flex-row gap-3">
                        <form action="{{ route('cart.store') }}" method="POST" class="flex-1 flex">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $product->id }}">
                            <label class="sr-only" for="qty">পরিমাণ</label>
                            <div class="qty-selector w-32 shrink-0">
                                <button type="button" class="qty-btn" onclick="updateQty(this, -1)">−</button>
                                <input type="hidden" name="quantity" value="1" class="qty-val">
                                <input id="qty" name="quantity" type="number" min="1" max="20" value="1" class="qty-input">
                                <button type="button" class="qty-btn" onclick="updateQty(this, 1)">+</button>
                            </div>
                            <button class="flex-1 btn btn-primary btn-md">কার্টে যোগ করুন</button>
                        </form>
                        <form action="{{ route('cart.store') }}" method="POST" class="flex-1 flex">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $product->id }}">
                            <input type="hidden" name="quantity" value="1">
                            <input type="hidden" name="buy_now" value="1">
                            <button class="flex-1 btn btn-cta btn-md text-sm font-medium">⚡ অর্ডার করুন</button>
                        </form>
                    </div>
                @endif
            </div>
            @else
            <div class="mt-6 flex items-center justify-center gap-2 text-red-700 font-medium">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>
                স্টক নেই — পণ্য চলে এলে জানাবেন
            </div>
            @endif
            <div class="mt-6 p-4 rounded-lg bg-moss text-sm text-forest/90 border border-moss-dark">
                <p class="font-medium">ক্যাশ অন ডেলিভারি সুবিধা আছে</p>
                <p class="mt-1">ঢাকার ভিতরে ৳60 · বাইরে ৳120 · পৌঁছালে টাকা দিন</p>
            </div>



            @if ($product->description)
            <div class="mt-6"><h2 class="font-medium">বর্ণনা</h2><div class="text-[15px] text-ink/80 leading-relaxed mt-2 whitespace-pre-line">{{ $product->description }}</div></div>
            @endif
        </div>
    </div>
    @if ($related->count())
    <div class="mt-10">
        <h2 class="section-title">আরও পছন্দের পণ্য</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-x-4 gap-y-8 mt-6">
            @foreach ($related as $rel) @include('components.product-card', ['product' => $rel]) @endforeach
        </div>
    </div>
    @endif
</section>
@push('scripts')
<script>
document.querySelectorAll('[data-thumb]').forEach(b => b.addEventListener('click', () => {
    document.getElementById('main-img').src = b.dataset.thumb;
}));
function updateQty(btn, delta) {
    const input = btn.closest('.qty-selector').querySelector('.qty-input');
    let val = parseInt(input.value) + delta;
    if (val < 1) val = 1;
    if (val > 20) val = 20;
    input.value = val;
}
</script>
@endpush
@endsection
