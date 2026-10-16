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
@endsection
