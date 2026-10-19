@props(['product'])

@php
    $img = $product->primaryImage();
    $src = $img?->path;
    if ($src && ! str_starts_with($src, 'http')) { $src = asset('storage/'.$src); }
    $src = $src ?: 'https://images.unsplash.com/photo-1441986300917-64674bd600d8?w=400&q=70&auto=format&fit=crop';
    $activeVariants = $product->relationLoaded('variants')
        ? $product->variants->where('is_active', true)
        : $product->variants()->where('is_active', true)->get();
    $hasVariants = $activeVariants->count() > 0;
    $soldOut = $activeVariants->isNotEmpty()
        ? $activeVariants->contains(fn ($v): bool => $v->stock_quantity > 0)
        : $product->stock_quantity > 0;
    $onSale = $product->discount_percent;
@endphp

<article class="product-card group">
    <div class="product-image-wrap">
        <a href="{{ route('products.show', $product->slug) }}" class="block h-full w-full" tabindex="-1">
            <img src="{{ $src }}" alt="{{ $img?->alt_text ?? $product->title }}" loading="lazy" width="400" height="500"
                 class="w-full h-full object-cover"
                 onerror="this.onerror=null;this.src='https://images.unsplash.com/photo-1441986300917-64674bd600d8?w=400&q=70&auto=format&fit=crop';">
        </a>
        @if ($onSale)
            <span class="product-badge badge badge-clay">−{{ $product->discount_percent }}%</span>
        @endif
        @if ($soldOut)
            <span class="product-stock-badge">স্টক নেই</span>
        @endif
        @if (! $soldOut && $hasVariants)
            <div class="absolute inset-x-3 bottom-3 hidden md:flex gap-2 md:translate-y-4 md:opacity-0 md:group-hover:translate-y-0 md:group-hover:opacity-100 transition duration-300 z-10">
                <form action="{{ route('cart.store') }}" method="POST" class="flex-1">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    <input type="hidden" name="quantity" value="1">
                    <button class="w-full btn btn-primary btn-sm text-[13px] whitespace-nowrap">কার্টে যোগ</button>
                </form>
                <a href="{{ route('products.show', $product->slug) }}" class="btn btn-outline btn-sm text-[13px] whitespace-nowrap">অর্ডার করুন</a>
            </div>
        @endif
    </div>
    <div class="pt-3">
        <p class="text-xs text-muted uppercase tracking-wider">{{ $product->category?->name ?? $product->brand }}</p>
        <h3 class="text-[15px] font-medium leading-snug mt-0.5">
            <a href="{{ route('products.show', $product->slug) }}" class="hover:text-forest transition-colors line-clamp-2">{{ $product->title }}</a>
        </h3>
        <p class="mt-1 text-[15px]">
            <span class="price">৳{{ number_format((float) $product->base_price, 0) }}</span>
            @if ($product->compare_at_price && (float) $product->compare_at_price > (float) $product->base_price)
                <span class="price-compare">৳{{ number_format((float) $product->compare_at_price, 0) }}</span>
                <span class="price-save">{{ $product->discount_percent }}% ছাড়</span>
            @endif
        </p>
        @if (! $soldOut)
        <div class="flex flex-col gap-2 sm:flex-row mt-3">
            @if ($hasVariants)
                <a href="{{ route('products.show', $product->slug) }}" class="flex-1 text-center border border-ink/20 text-[13px] font-medium px-3 py-2 rounded-md hover:border-forest hover:text-forest transition-colors whitespace-nowrap">কার্টে যোগ</a>
                <a href="{{ route('products.show', $product->slug) }}" class="flex-1 text-center btn btn-cta btn-sm text-[13px] whitespace-nowrap">অর্ডার করুন</a>
            @else
                <form action="{{ route('cart.store') }}" method="POST" class="flex-1">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    <input type="hidden" name="quantity" value="1">
                    <button class="w-full border border-ink/20 text-[13px] font-medium px-3 py-2 rounded-md hover:border-forest hover:text-forest transition-colors whitespace-nowrap">কার্টে যোগ</button>
                </form>
                <form action="{{ route('cart.store') }}" method="POST" class="flex-1">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    <input type="hidden" name="quantity" value="1">
                    <input type="hidden" name="buy_now" value="1">
                    <button class="w-full btn btn-cta btn-sm text-[13px] whitespace-nowrap">অর্ডার করুন</button>
                </form>
            @endif
        </div>
        @endif
    </div>
</article>
@stack('styles')