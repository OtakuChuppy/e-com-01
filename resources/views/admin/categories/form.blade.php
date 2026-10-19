@extends('layouts.admin')
@section('title', ($category->exists ? 'Edit' : 'New').' category — Admin')
@section('content')
<h1 class="font-display text-3xl tracking-tight">{{ $category->exists ? 'Edit category' : 'New category' }}</h1>
<p class="text-sm text-muted mt-1">Categories group related products so shoppers can browse by department.</p>

<form action="{{ $category->exists ? route('admin.categories.update', $category) : route('admin.categories.store') }}" method="POST" class="mt-5 max-w-2xl bg-white border border-ink/10 rounded p-5">
    @csrf @if($category->exists) @method('PATCH') @endif
    <div class="grid sm:grid-cols-2 gap-4">
        <div>
            <label class="text-sm font-medium" for="name">Name *</label>
            <input id="name" name="name" required value="{{ old('name', $category->name) }}" class="mt-1 w-full border border-ink/15 rounded px-3 py-2 text-sm">
        </div>
        <div>
            <label class="text-sm font-medium" for="slug">Slug *</label>
            <input id="slug" name="slug" required value="{{ old('slug', $category->slug) }}" placeholder="e.g. kitchen-dining" class="mt-1 w-full border border-ink/15 rounded px-3 py-2 text-sm">
            <p class="text-xs text-muted mt-1">Used in the category URL.</p>
        </div>
        <div class="sm:col-span-2">
            <label class="text-sm font-medium" for="parent_id">Parent category (optional)</label>
            <select id="parent_id" name="parent_id" class="mt-1 w-full border border-ink/15 rounded px-3 py-2 text-sm bg-white">
                <option value="">— Top level —</option>
                @php $top = \App\Models\Category::whereNull('parent_id')->orderBy('name')->get(); @endphp
                @foreach($top as $t)
                @if((string)old('parent_id', $category->parent_id)===(string)$t->id && $t->id !== $category->id)
                <option value="{{ $t->id }}" selected>{{ $t->name }}</option>
                @else
                <option value="{{ $t->id }}">{{ $t->name }}</option>
                @endif
                @endforeach
            </select>
        </div>
        <div class="sm:col-span-2">
            <label class="text-sm font-medium" for="description">Description</label>
            <textarea id="description" name="description" rows="3" class="mt-1 w-full border border-ink/15 rounded px-3 py-2 text-sm">{{ old('description', $category->description) }}</textarea>
        </div>
        <div>
            <label class="text-sm font-medium" for="sort_order">Sort order</label>
            <input id="sort_order" name="sort_order" type="number" min="0" value="{{ old('sort_order', $category->sort_order ?? 0) }}" class="mt-1 w-full border border-ink/15 rounded px-3 py-2 text-sm">
            <p class="text-xs text-muted mt-1">Higher numbers appear later in lists.</p>
        </div>
        <div class="flex items-center gap-2 pb-1">
            <input type="checkbox" id="is_active" name="is_active" value="1" @checked(old('is_active', $category->is_active ?? true)) class="rounded">
            <label for="is_active" class="text-sm">Active (shows on storefront)</label>
        </div>
    </div>

    <div class="sm:col-span-2 border-t border-ink/10 pt-4 mt-1 flex gap-3">
        <button class="bg-forest text-white text-sm font-medium px-6 py-2.5 rounded hover:bg-forest-deep transition-colors">{{ $category->exists ? 'Save changes' : 'Create category' }}</button>
        <a href="{{ route('admin.categories.index') }}" class="text-sm px-4 py-2.5 border border-ink/15 rounded hover:bg-linen">Cancel</a>
    </div>
</form>
@endsection
