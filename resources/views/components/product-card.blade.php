@props(['product'])

@php
    $img = $product->primaryImage();
    $src = $img?->path;
    if ($src && ! str_starts_with($src, 'http')) { $src = asset('storage/'.$src); }
    $src = $src ?: 'https://images.unsplash.com/photo-1441986300917-64674bd600d8?w=600&q=80&auto=format&fit=crop';
    $hasVariants = $product->relationLoaded('variants')
        ? $product->variants->where('is_active', true)->count() > 0
        : $product->variants()->where('is_active', true)->exists();
    $soldOut = ! $product->in_stock;
@endphp

<article class="group">
    <div class="relative overflow-hidden rounded bg-linen aspect-4/5" tabindex="0">
        <a href="{{ route('products.show', $product->slug) }}" class="block h-full w-full" tabindex="-1">
            <img src="{{ $src }}" alt="{{ $img?->alt_text ?? $product->title }}" loading="lazy" width="600" height="750"
                 class="w-full h-full object-cover transition duration-300 group-hover:scale-[1.03]"
                 onerror="this.onerror=null;this.src='https://images.unsplash.com/photo-1441986300917-64674bd600d8?w=600&q=80&auto=format&fit=crop';">
        </a>
        @if ($product->discount_percent)
            <span class="absolute top-3 left-3 bg-ivory/95 text-forest text-xs font-medium px-2 py-1 rounded">−{{ $product->discount_percent }}%</span>
        @endif
        @if ($soldOut)
            <span class="absolute inset-x-3 bottom-3 bg-ink/85 text-white text-xs text-center px-2 py-1.5 rounded">Out of stock</span>
        @endif

        {{-- Quick actions: reveal on hover / focus (forms need to stay out of the image link) --}}
        @if (! $soldOut)
        <div class="absolute inset-x-3 bottom-3 flex gap-2 md:translate-y-2 md:opacity-0 md:group-hover:translate-y-0 md:group-hover:opacity-100 md:group-focus-within:translate-y-0 md:group-focus-within:opacity-100 transition duration-200 z-10">
            @if ($hasVariants)
                <span class="flex-1 grid grid-cols-1 gap-2">
                    <a href="{{ route('products.show', $product->slug) }}" class="text-center bg-ivory/95 backdrop-blur text-ink text-xs font-semibold px-3 py-2.5 rounded-lg shadow hover:bg-white">Add to Cart</a>
                    <a href="{{ route('products.show', $product->slug) }}" class="order-btn text-center text-xs font-bold px-3 py-2.5 rounded-lg shadow-lg">⚡ Order Now</a>
                </span>
            @else
                <form action="{{ route('cart.store') }}" method="POST" class="flex-1">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    <input type="hidden" name="quantity" value="1">
                    <button class="w-full bg-ivory/95 backdrop-blur text-ink text-xs font-semibold px-3 py-2.5 rounded-lg shadow hover:bg-white">Add to Cart</button>
                </form>
                <form action="{{ route('cart.store') }}" method="POST" class="flex-1">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    <input type="hidden" name="quantity" value="1">
                    <input type="hidden" name="buy_now" value="1">
                    <button class="order-btn w-full text-xs font-bold px-3 py-2.5 rounded-lg shadow-lg">⚡ Order Now</button>
                </form>
            @endif
        </div>
        @endif
    </div>
    <div class="pt-3">
        <p class="text-xs text-muted">{{ $product->category?->name ?? $product->brand }}</p>
        <h3 class="text-[15px] font-medium leading-snug mt-0.5">
            <a href="{{ route('products.show', $product->slug) }}" class="hover:text-forest line-clamp-2">{{ $product->title }}</a>
        </h3>
        <p class="mt-1 text-[15px]">
            <span class="font-semibold">৳{{ number_format((float) $product->base_price, 0) }}</span>
            @if ($product->compare_at_price && (float) $product->compare_at_price > (float) $product->base_price)
                <span class="text-muted line-through text-sm ml-1.5">৳{{ number_format((float) $product->compare_at_price, 0) }}</span>
            @endif
        </p>
        {{-- Compact two-button row under the card (thumb-friendly on mobile) --}}
        @if (! $soldOut)
        <div class="flex gap-2 mt-2.5">
            @if ($hasVariants)
                <a href="{{ route('products.show', $product->slug) }}" class="flex-1 text-center border border-ink/20 text-[13px] font-medium px-3 py-2 rounded-lg hover:border-forest hover:text-forest">Add to Cart</a>
                <a href="{{ route('products.show', $product->slug) }}" class="order-btn order-btn-sm flex-1 text-center text-[13px] font-bold px-3 py-2 rounded-lg">Order Now</a>
            @else
                <form action="{{ route('cart.store') }}" method="POST" class="flex-1">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    <input type="hidden" name="quantity" value="1">
                    <button class="w-full border border-ink/20 text-[13px] font-medium px-3 py-2 rounded-lg hover:border-forest hover:text-forest">Add to Cart</button>
                </form>
                <form action="{{ route('cart.store') }}" method="POST" class="flex-1">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    <input type="hidden" name="quantity" value="1">
                    <input type="hidden" name="buy_now" value="1">
                    <button class="order-btn order-btn-sm w-full text-[13px] font-bold px-3 py-2 rounded-lg">Order Now</button>
                </form>
            @endif
        </div>
        @endif
    </div>
</article>
@stack('styles')