@props(['product'])

@php
    $img = $product->primaryImage();
    $src = $img?->path;
    if ($src && ! str_starts_with($src, 'http')) { $src = asset('storage/'.$src); }
    $src = $src ?: 'https://images.unsplash.com/photo-1441986300917-64674bd600d8?w=600&q=80&auto=format&fit=crop';
@endphp

<article class="group">
    <a href="{{ route('products.show', $product->slug) }}" class="block overflow-hidden rounded bg-linen aspect-[4/5] relative" tabindex="0">
        <img src="{{ $src }}" alt="{{ $img?->alt_text ?? $product->title }}" loading="lazy" width="600" height="750"
             class="w-full h-full object-cover transition duration-300 group-hover:scale-[1.03]"
             onerror="this.onerror=null;this.src='https://images.unsplash.com/photo-1441986300917-64674bd600d8?w=600&q=80&auto=format&fit=crop';">
        @if ($product->discount_percent)
            <span class="absolute top-3 left-3 bg-ivory/95 text-forest text-xs font-medium px-2 py-1 rounded">−{{ $product->discount_percent }}%</span>
        @endif
        @if (! $product->in_stock)
            <span class="absolute inset-x-3 bottom-3 bg-ink/85 text-white text-xs text-center px-2 py-1.5 rounded">Out of stock</span>
        @endif
    </a>
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
    </div>
</article>
