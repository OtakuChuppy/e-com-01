@extends('layouts.store')
@section('title', $product->meta_title ?: $product->title.' — '.config('shop.name'))
@section('meta_description', $product->meta_description ?: \Illuminate\Support\Str::limit($product->short_description ?? $product->description ?? '', 160))
@section('content')
<section class="max-w-7xl mx-auto px-4 sm:px-6 pt-8">
    <p class="text-xs text-muted"><a href="{{ route('home') }}" class="hover:text-forest">Home</a> / <a href="{{ route('products.index') }}" class="hover:text-forest">Shop</a> / {{ $product->title }}</p>
    <div class="grid md:grid-cols-2 gap-8 md:gap-12 mt-4">
        <div>
            @php $imgs = $product->images; $first = $imgs->first()?->path; if ($first && ! str_starts_with($first, 'http')) $first = asset('storage/'.$first); @endphp
            <div class="rounded bg-linen aspect-4/5 overflow-hidden">
                <img id="main-img" src="{{ $first ?? 'https://images.unsplash.com/photo-1441986300917-64674bd600d8?w=800&q=80&auto=format&fit=crop' }}" alt="{{ $product->title }}" class="w-full h-full object-cover" width="800" height="1000" fetchpriority="high" onerror="this.onerror=null;this.src='https://images.unsplash.com/photo-1441986300917-64674bd600d8?w=800&q=80&auto=format&fit=crop';">
            </div>
            @if ($imgs->count() > 1)
            <div class="grid grid-cols-4 gap-2 mt-2">
                @foreach ($imgs as $im)
                @php $s = $im->path; if (! str_starts_with($s, 'http')) $s = asset('storage/'.$s); @endphp
                <button data-thumb="{{ $s }}" class="rounded bg-linen aspect-square overflow-hidden" aria-label="View image"><img src="{{ $s }}" alt="{{ $im->alt_text ?? $product->title }}" class="w-full h-full object-cover" loading="lazy"></button>
                @endforeach
            </div>
            @endif
        </div>
        <div>
            <p class="text-xs text-muted">{{ $product->category?->name }} @if($product->brand)· {{ $product->brand }}@endif</p>
            <h1 class="font-display text-3xl md:text-4xl tracking-tight mt-1">{{ $product->title }}</h1>
            <p class="mt-3 text-2xl"><span class="font-semibold">৳{{ number_format((float) $product->base_price, 0) }}</span>
                @if ($product->compare_at_price && (float) $product->compare_at_price > (float) $product->base_price)
                <span class="text-muted line-through text-lg ml-2">৳{{ number_format((float) $product->compare_at_price, 0) }}</span>
                <span class="text-xs bg-moss text-forest px-2 py-1 rounded ml-2 align-middle">Save {{ $product->discount_percent }}%</span>
                @endif
            </p>
            <p class="text-sm mt-2">@if($product->in_stock)<span class="text-forest font-medium">In stock</span>@else<span class="text-red-700 font-medium">Out of stock</span>@endif <span class="text-muted">· SKU {{ $product->sku }}</span></p>
            @if ($product->short_description)<p class="text-[15px] text-ink/80 leading-relaxed mt-4">{{ $product->short_description }}</p>@endif
            <form action="{{ route('cart.store') }}" method="POST" class="mt-6 space-y-4">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id }}">
                @if ($product->variants->count())
                <div>
                    <label for="variant_id" class="text-sm font-medium">Option</label>
                    <select id="variant_id" name="variant_id" class="mt-1 w-full border border-ink/15 rounded px-3 py-2.5 text-sm bg-white" required>
                        @foreach ($product->variants->where('is_active', true) as $v)
                        <option value="{{ $v->id }}">{{ $v->name }} — ৳{{ number_format($v->effectivePrice(), 0) }}{{ $v->stock_quantity > 0 ? '' : ' (out of stock)' }}</option>
                        @endforeach
                    </select>
                </div>
                @endif
                <div class="flex gap-3">
                    <label class="sr-only" for="quantity">Quantity</label>
                    <input id="quantity" name="quantity" type="number" min="1" max="20" value="1" class="w-20 border border-ink/15 rounded px-3 py-2.5 text-sm bg-white text-center">
                    <button @disabled(! $product->in_stock) class="flex-1 bg-forest text-white font-medium px-6 py-2.5 rounded disabled:opacity-40">{{ $product->in_stock ? 'Add to cart' : 'Out of stock' }}</button>
                </div>
                @error('quantity')<p class="text-sm text-red-700">{{ $message }}</p>@enderror
            </form>
            <div class="text-sm text-muted mt-6 space-y-1.5 border-t border-ink/10 pt-5">
                <p>Cash on Delivery available · ৳60 inside Dhaka, ৳120 outside.</p>
                <p>Order lookup available with reference + phone number.</p>
            </div>
            @if ($product->description)
            <div class="mt-6"><h2 class="font-medium">Description</h2><div class="text-[15px] text-ink/80 leading-relaxed mt-2 whitespace-pre-line">{{ $product->description }}</div></div>
            @endif
        </div>
    </div>
    @if ($related->count())
    <div class="mt-16"><h2 class="font-display text-2xl tracking-tight">You may also like</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-x-4 gap-y-8 mt-5">
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
</script>
@endpush
@endsection
