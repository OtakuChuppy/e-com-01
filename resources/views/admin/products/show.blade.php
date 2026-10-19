@extends('layouts.admin')
@section('title', $product->title.' — Admin')
@section('content')
<p class="text-xs text-muted"><a href="{{ route('admin.products.index') }}" class="hover:text-forest">Products</a> / {{ $product->sku }}</p>
<h1 class="font-display text-3xl tracking-tight mt-1">{{ $product->title }}</h1>
<p class="text-sm text-muted mt-1">SKU {{ $product->sku }} · <span class="capitalize">{{ $product->status }}</span> · Stock {{ $product->stock_quantity }} · ৳{{ number_format((float)$product->base_price,0) }}</p>
<div class="flex flex-wrap gap-2 mt-4">
    <a href="{{ route('admin.products.edit', $product) }}" class="border border-ink/20 text-sm px-4 py-2 rounded">Edit</a>
    <form action="{{ route('admin.products.status', $product) }}" method="POST">@csrf @method('PATCH')<input type="hidden" name="status" value="published"><button class="border border-ink/20 text-sm px-4 py-2 rounded">Publish</button></form>
    <form action="{{ route('admin.products.status', $product) }}" method="POST">@csrf @method('PATCH')<input type="hidden" name="status" value="archived"><button class="border border-ink/20 text-sm px-4 py-2 rounded">Archive</button></form>
    <form action="{{ route('admin.products.destroy', $product) }}" method="POST" onsubmit="return confirm('Delete {{ $product->sku }}? Blocked automatically if it appears in past orders.');">@csrf @method('DELETE')<button class="border border-red-300 text-red-700 text-sm px-4 py-2 rounded">Delete</button></form>
</div>
<div class="grid sm:grid-cols-2 gap-4 mt-5">
    <div class="bg-white border border-ink/10 rounded p-5 text-sm space-y-1">
        <h2 class="font-medium">Details</h2>
        <p>Category: {{ $product->category?->name ?? '—' }}</p>
        <p>Brand: {{ $product->brand ?? '—' }}</p>
        <p>Short: {{ $product->short_description ?? '—' }}</p>
        <p>Featured: {{ $product->is_featured ? 'Yes' : 'No' }} · Demo: {{ $product->is_demo ? 'Yes' : 'No' }}</p>
    </div>
    <div class="bg-white border border-ink/10 rounded p-5 text-sm">
        <h2 class="font-medium">Variants ({{ $product->variants->count() }})</h2>
        @if($product->variants->isEmpty())<p class="text-muted mt-1">No variants — stock tracked on product.</p>@endif
        <ul class="mt-1 space-y-1">@foreach($product->variants as $v)<li>{{ $v->name }} · {{ $v->sku }} · stock {{ $v->stock_quantity }}</li>@endforeach</ul>
    </div>
</div>
<div class="bg-white border border-ink/10 rounded p-5 mt-4">
    <h2 class="font-medium">Images ({{ $product->images->count() }})</h2>
    <p class="text-xs text-muted mt-1">JPG, PNG or WebP · max 2 MB each · up to 6 per upload. First image is the product's main photo until you set another one as primary.</p>
    <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-6 gap-3 mt-3">
        @forelse($product->images as $im)
        @php $s = $im->path; if (! str_starts_with($s, 'http')) $s = asset('storage/'.$s); @endphp
        <div class="relative border border-ink/10 rounded overflow-hidden group">
            <img src="{{ $s }}" alt="{{ $im->alt_text ?? $product->title }}" class="w-full aspect-square object-cover" loading="lazy">
            @if($im->is_primary)<span class="absolute top-1 left-1 bg-forest text-white text-[10px] px-1.5 py-0.5 rounded">Primary</span>@endif
            <div class="absolute inset-x-0 bottom-0 bg-black/55 text-white text-[11px] flex divide-x divide-white/30 opacity-0 group-hover:opacity-100 transition-opacity">
                @if(!$im->is_primary)<form action="{{ route('admin.products.images.primary', [$product, $im]) }}" method="POST" class="flex-1">@csrf @method('PATCH')<button class="w-full py-1.5 hover:bg-black/70">Set primary</button></form>@endif
                <form action="{{ route('admin.products.images.destroy', [$product, $im]) }}" method="POST" class="flex-1" onsubmit="return confirm('Delete this image?');">@csrf @method('DELETE')<button class="w-full py-1.5 hover:bg-black/70 text-red-300">Delete</button></form>
            </div>
        </div>
        @empty
        <p class="col-span-full text-sm text-muted border border-dashed border-ink/20 rounded p-4 text-center">No images yet — upload some below so the storefront shows this product properly.</p>
        @endforelse
    </div>
    <form action="{{ route('admin.products.images.store', $product) }}" method="POST" enctype="multipart/form-data" class="mt-4 flex flex-wrap items-end gap-3 border-t border-ink/10 pt-4">
        @csrf
        <div class="flex-1 min-w-50"><label class="text-sm font-medium" for="images">Add images</label><input id="images" name="images[]" type="file" accept="image/jpeg,image/png,image/webp" multiple required class="mt-1 block w-full text-sm"></div>
        <div class="min-w-40"><label class="text-sm font-medium" for="alt_text">Alt text (optional)</label><input id="alt_text" name="alt_text" maxlength="120" placeholder="Describe the images" class="mt-1 w-full border border-ink/15 rounded px-3 py-2 text-sm"></div>
        <button class="bg-forest text-white text-sm font-medium px-4 py-2 rounded">Upload</button>
    </form>
</div>
@endsection