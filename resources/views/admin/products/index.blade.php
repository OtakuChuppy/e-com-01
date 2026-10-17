@extends('layouts.admin')
@section('title', 'Products — Admin')
@section('content')
<div class="flex items-center justify-between">
<div><h1 class="font-display text-3xl tracking-tight">Products</h1>
<p class="text-sm text-muted mt-1">@if($lowStock > 0)<span class="text-clay font-medium">{{ $lowStock }} low-stock</span> · @endif Archive instead of deleting items in past orders.</p></div>
<a href="{{ route('admin.products.create') }}" class="bg-forest text-white text-sm px-4 py-2 rounded">New product</a>
</div>
<form method="GET" class="flex flex-wrap gap-2 mt-4">
<input name="q" value="{{ request('q') }}" placeholder="Title or SKU…" class="border border-ink/15 rounded px-3 py-2 text-sm bg-white w-52">
<select name="status" class="border border-ink/15 rounded px-3 py-2 text-sm bg-white"><option value="">All statuses</option>
@foreach (['draft','published','archived'] as $s)<option value="{{ $s }}" @selected(request('status')===$s)>{{ ucfirst($s) }}</option>@endforeach</select>
<button class="bg-forest text-white text-sm px-4 py-2 rounded">Filter</button>
</form>
@if ($products->isEmpty())
<p class="text-sm text-muted border border-dashed border-ink/20 rounded p-6 text-center mt-4">No products found.</p>
@else
<div class="bg-white border border-ink/10 rounded overflow-x-auto mt-4">
<table class="w-full text-sm min-w-170">
<thead><tr class="text-left text-xs text-muted border-b border-ink/10"><th class="px-4 py-2">Product</th><th class="px-4 py-2">SKU</th><th class="px-4 py-2">Price</th><th class="px-4 py-2">Stock</th><th class="px-4 py-2">Status</th></tr></thead>
<tbody>@foreach ($products as $p)
<tr class="border-b border-ink/5 last:border-0">
<td class="px-4 py-2"><a class="text-forest underline underline-offset-4" href="{{ route('admin.products.show', $p) }}">{{ $p->title }}</a>@if($p->stock_quantity <= $p->low_stock_threshold) <span class="text-xs text-clay">· low</span>@endif</td>
<td class="px-4 py-2 text-muted">{{ $p->sku }}</td>
<td class="px-4 py-2">৳{{ number_format((float)$p->base_price,0) }}</td>
<td class="px-4 py-2">{{ $p->stock_quantity }}</td>
<td class="px-4 py-2 capitalize">{{ $p->status }}</td>
</tr>@endforeach</tbody></table></div>
<div class="mt-4">{{ $products->links() }}</div>
@endif
@endsection
