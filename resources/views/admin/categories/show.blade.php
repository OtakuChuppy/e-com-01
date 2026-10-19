@extends('layouts.admin')
@section('title', $category->name.' — Category · Admin')
@section('content')
<div class="flex items-center justify-between">
    <div>
        <p class="text-xs text-muted"><a href="{{ route('admin.categories.index') }}" class="hover:text-forest">Categories</a> / {{ $category->name }}</p>
        <h1 class="font-display text-3xl tracking-tight mt-1">{{ $category->name }}</h1>
        <p class="text-sm text-muted mt-1">{{ $category->description ?? 'No description yet.' }}</p>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('admin.categories.edit', $category) }}" class="border border-ink/20 text-sm px-4 py-2 rounded hover:bg-linen">Edit category</a>
        <span class="inline-block px-2.5 py-0.5 rounded text-xs font-medium {{ $category->is_active ? 'bg-green-100 text-green-700 border border-green-200/60' : 'bg-gray-100 text-gray-600 border border-gray-200/60' }}">
            {{ $category->is_active ? 'Active' : 'Inactive' }}
        </span>
        <a class="text-xs border border-ink/15 rounded px-2 py-1 hover:bg-linen" href="{{ route('admin.products.index') }}?category_id={{ $category->id }}">View all products</a>
    </div>
</div>

<div class="grid grid-cols-2 md:grid-cols-3 gap-3 mt-4">
    <div class="bg-white border border-ink/10 rounded p-4">
        <p class="text-xs text-muted">Products in category</p>
        <p class="text-2xl font-semibold mt-1 text-forest">{{ $category->products->count() }}</p>
    </div>
    <div class="bg-white border border-ink/10 rounded p-4">
        <p class="text-xs text-muted">Published</p>
        <p class="text-2xl font-semibold mt-1">{{ $category->products->where('status', 'published')->count() }}</p>
    </div>
    <div class="bg-white border border-ink/10 rounded p-4">
        <p class="text-xs text-muted">Draft / archived</p>
        <p class="text-2xl font-semibold mt-1">{{ $category->products->where('status', '!=', 'published')->count() }}</p>
    </div>
</div>

@if ($category->children->count())
<div class="bg-white border border-ink/10 rounded p-4 mt-4">
    <h2 class="font-medium mb-3">Sub-categories</h2>
    <div class="flex flex-wrap gap-2">
        @foreach ($category->children as $child)
        <a class="text-sm border border-ink/15 rounded px-3 py-1 hover:bg-linen text-forest" href="{{ route('admin.categories.show', $child) }}">{{ $child->name }}</a>
        @endforeach
    </div>
</div>
@endif

@if ($category->products->isEmpty())
<p class="text-sm text-muted border border-dashed border-ink/20 rounded p-6 text-center mt-4">No products in this category yet. <a class="text-forest underline underline-offset-4" href="{{ route('admin.products.create') }}?category_id={{ $category->id }}">Add a product</a>.</p>
@else
<div class="bg-white border border-ink/10 rounded overflow-x-auto mt-4">
    <table class="w-full text-sm min-w-220">
        <thead>
            <tr class="text-left text-xs text-muted border-b border-ink/10">
                <th class="px-4 py-2">Product</th>
                <th class="px-4 py-2">SKU</th>
                <th class="px-4 py-2 text-right">Price</th>
                <th class="px-4 py-2 text-center">Stock</th>
                <th class="px-4 py-2">Status</th>
                <th class="px-4 py-2 text-right">Actions</th>
            </tr>
        </thead>
        <tbody>
        @foreach ($category->products as $p)
        <tr class="border-b border-ink/5 last:border-0">
            <td class="px-4 py-2"><a class="text-forest underline underline-offset-4" href="{{ route('admin.products.show', $p) }}">{{ $p->title }}</a></td>
            <td class="px-4 py-2 text-muted">{{ $p->sku }}</td>
            <td class="px-4 py-2 text-right">৳{{ number_format((float)$p->base_price,0) }}</td>
            <td class="px-4 py-2 text-center">{{ $p->stock_quantity }}</td>
            <td class="px-4 py-2">
                <span class="inline-block px-2 py-0.5 rounded text-xs font-medium {{ $p->status === 'published' ? 'bg-green-100 text-green-700 border border-green-200' : ($p->status === 'draft' ? 'bg-gray-100 text-gray-600 border border-gray-200' : 'bg-amber-100 text-amber-800 border border-amber-200') }}">
                    {{ ucfirst($p->status) }}
                </span>
            </td>
            <td class="px-4 py-2 text-right">
                <a class="text-xs border border-ink/15 rounded px-2 py-1 hover:bg-linen" href="{{ route('admin.products.show', $p) }}">Open</a>
            </td>
        </tr>
        @endforeach
        </tbody>
    </table>
</div>
@endif
@endsection
