@extends('layouts.admin')
@section('title', 'Categories — '.config('shop.name'))
@section('content')
<div class="flex items-center justify-between">
    <div>
        <h1 class="font-display text-3xl tracking-tight">Categories</h1>
        <p class="text-sm text-muted mt-1">Organise products so shoppers can browse by department.</p>
    </div>
    <a href="{{ route('admin.categories.create') }}" class="bg-forest text-white text-sm px-4 py-2 rounded hover:bg-forest-deep transition-colors">New category</a>
</div>

<form method="GET" class="flex flex-wrap gap-2 mt-4">
    <input name="q" value="{{ request('q') }}" placeholder="Search categories…" class="border border-ink/15 rounded px-3 py-2 text-sm bg-white w-64">
    <button class="bg-forest text-white text-sm px-4 py-2 rounded hover:bg-forest-deep transition-colors">Filter</button>
</form>

@if ($categories->isEmpty())
<p class="text-sm text-muted border border-dashed border-ink/20 rounded p-6 text-center mt-4">No categories found.</p>
@else
<div class="bg-white border border-ink/10 rounded overflow-x-auto mt-4">
    <table class="w-full text-sm min-w-200">
        <thead>
            <tr class="text-left text-xs text-muted border-b border-ink/10">
                <th class="px-4 py-2">Category</th>
                <th class="px-4 py-2">Slug</th>
                <th class="px-4 py-2 text-center">Products</th>
                <th class="px-4 py-2 text-center">Active</th>
                <th class="px-4 py-2 text-right">Sort order</th>
                <th class="px-4 py-2 text-right">Actions</th>
            </tr>
        </thead>
        <tbody>
        @foreach ($categories as $c)
        <tr class="border-b border-ink/5 last:border-0 hover:bg-linen/50 transition-colors">
            <td class="px-4 py-2">
                <a class="text-forest underline underline-offset-4 font-medium" href="{{ route('admin.categories.show', $c) }}">{{ $c->name }}</a>
                @if ($c->parent)
                <span class="text-xs text-muted block mt-0.5">Parent: <a class="text-forest hover:underline" href="{{ route('admin.categories.show', $c->parent) }}">{{ $c->parent->name }}</a></span>
                @endif
                @if ($c->children->count())
                <span class="text-xs text-muted block mt-0.5">{{ $c->children->count() }} sub-category{{ $c->children->count() > 1 ? 'ies' : '' }}</span>
                @endif
            </td>
            <td class="px-4 py-2 text-muted">{{ $c->slug }}</td>
            <td class="px-4 py-2 text-center"><a class="text-forest underline underline-offset-4" href="{{ route('admin.categories.show', $c) }}">{{ $c->products_count }}</a></td>
            <td class="px-4 py-2 text-center">
                <span class="inline-block px-2 py-0.5 rounded text-xs font-medium {{ $c->is_active ? 'bg-green-100 text-green-700 border border-green-200' : 'bg-gray-100 text-gray-600 border border-gray-200' }}">
                    {{ $c->is_active ? 'Active' : 'Inactive' }}
                </span>
            </td>
            <td class="px-4 py-2 text-right text-muted">{{ $c->sort_order }}</td>
            <td class="px-4 py-2 text-right">
                <a class="text-xs border border-ink/15 rounded px-2 py-1 hover:bg-linen" href="{{ route('admin.categories.edit', $c) }}">Edit</a>
                <form action="{{ route('admin.categories.destroy', $c) }}" method="POST" class="inline" onsubmit="return confirm('Delete category {{ $c->name }}? This is blocked if products still belong to it.');">
                    @csrf @method('DELETE')
                    <button class="text-xs border border-red-200 text-red-600 rounded px-2 py-1 hover:bg-red-50">Delete</button>
                </form>
            </td>
        </tr>
        @endforeach
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $categories->links() }}</div>
@endif
@endsection
