@extends('layouts.admin')
@section('title', ($product->exists ? 'Edit' : 'New').' product — Admin')
@section('content')
<h1 class="font-display text-3xl tracking-tight">{{ $product->exists ? 'Edit product' : 'New product' }}</h1>
<form action="{{ $product->exists ? route('admin.products.update', $product) : route('admin.products.store') }}" method="POST" class="mt-5 grid sm:grid-cols-2 gap-4 bg-white border border-ink/10 rounded p-5">
    @csrf @if($product->exists) @method('PATCH') @endif
    <div><label class="text-sm font-medium" for="title">Title *</label><input id="title" name="title" required value="{{ old('title', $product->title) }}" class="mt-1 w-full border border-ink/15 rounded px-3 py-2 text-sm"></div>
    <div><label class="text-sm font-medium" for="sku">SKU *</label><input id="sku" name="sku" required value="{{ old('sku', $product->sku) }}" class="mt-1 w-full border border-ink/15 rounded px-3 py-2 text-sm"></div>
    <div><label class="text-sm font-medium" for="category_id">Category</label><select id="category_id" name="category_id" class="mt-1 w-full border border-ink/15 rounded px-3 py-2 text-sm bg-white">
            <option value="">—</option>@foreach($categories as $c)<option value="{{ $c->id }}" @selected((string)old('category_id', $product->category_id)===(string)$c->id)>{{ $c->name }}</option>@endforeach
        </select></div>
    <div><label class="text-sm font-medium" for="brand">Brand</label><input id="brand" name="brand" value="{{ old('brand', $product->brand) }}" class="mt-1 w-full border border-ink/15 rounded px-3 py-2 text-sm"></div>
    <div><label class="text-sm font-medium" for="base_price">Price (৳) *</label><input id="base_price" name="base_price" type="number" step="0.01" min="0" required value="{{ old('base_price', $product->base_price) }}" class="mt-1 w-full border border-ink/15 rounded px-3 py-2 text-sm"></div>
    <div><label class="text-sm font-medium" for="compare_at_price">Compare-at price (optional)</label><input id="compare_at_price" name="compare_at_price" type="number" step="0.01" min="0" value="{{ old('compare_at_price', $product->compare_at_price) }}" class="mt-1 w-full border border-ink/15 rounded px-3 py-2 text-sm"></div>
    <div><label class="text-sm font-medium" for="stock_quantity">Stock *</label><input id="stock_quantity" name="stock_quantity" type="number" min="0" required value="{{ old('stock_quantity', $product->stock_quantity ?? 0) }}" class="mt-1 w-full border border-ink/15 rounded px-3 py-2 text-sm"></div>
    <div><label class="text-sm font-medium" for="low_stock_threshold">Low-stock alert at</label><input id="low_stock_threshold" name="low_stock_threshold" type="number" min="0" value="{{ old('low_stock_threshold', $product->low_stock_threshold ?? 5) }}" class="mt-1 w-full border border-ink/15 rounded px-3 py-2 text-sm"></div>
    <div><label class="text-sm font-medium" for="status">Status *</label><select id="status" name="status" class="mt-1 w-full border border-ink/15 rounded px-3 py-2 text-sm bg-white">@foreach(['draft','published','archived'] as $s)<option value="{{ $s }}" @selected(old('status', $product->status ?? 'draft')===$s)>{{ ucfirst($s) }}</option>@endforeach</select></div>
    <div class="flex items-end gap-2 pb-1"><input type="checkbox" id="is_featured" name="is_featured" value="1" @checked(old('is_featured', $product->is_featured)) class="rounded"><label for="is_featured" class="text-sm">Featured on homepage</label></div>
    <div class="sm:col-span-2"><label class="text-sm font-medium" for="short_description">Short description</label><input id="short_description" name="short_description" maxlength="500" value="{{ old('short_description', $product->short_description) }}" class="mt-1 w-full border border-ink/15 rounded px-3 py-2 text-sm"></div>
    <div class="sm:col-span-2"><label class="text-sm font-medium" for="description">Description</label><textarea id="description" name="description" rows="4" class="mt-1 w-full border border-ink/15 rounded px-3 py-2 text-sm">{{ old('description', $product->description) }}</textarea></div>
    <div class="sm:col-span-2 border-t border-ink/10 pt-4 mt-1">
        <p class="text-xs font-semibold uppercase tracking-wide text-muted">SEO (how this product appears in Google)</p>
        <p class="text-xs text-muted mt-0.5">Leave blank to fall back to the title and short description automatically.</p>
    </div>
    <div class="sm:col-span-2"><label class="text-sm font-medium" for="meta_title">Meta title</label><input id="meta_title" name="meta_title" maxlength="255" value="{{ old('meta_title', $product->meta_title) }}" placeholder="e.g. Steel Tiffin Carrier 3-Tier — DeshiMart Demo" class="mt-1 w-full border border-ink/15 rounded px-3 py-2 text-sm">
        <p class="text-xs text-muted mt-1">Aim for ~50–60 characters.</p>
    </div>
    <div class="sm:col-span-2"><label class="text-sm font-medium" for="meta_description">Meta description</label><textarea id="meta_description" name="meta_description" rows="2" maxlength="500" placeholder="Short summary shown under the title in search results…" class="mt-1 w-full border border-ink/15 rounded px-3 py-2 text-sm">{{ old('meta_description', $product->meta_description) }}</textarea>
        <p class="text-xs text-muted mt-1">Aim for ~150–160 characters.</p>
    </div>
    <div class="sm:col-span-2 flex gap-3"><button class="bg-forest text-white text-sm font-medium px-6 py-2.5 rounded">{{ $product->exists ? 'Save changes' : 'Create product' }}</button>
        <a href="{{ route('admin.products.index') }}" class="text-sm px-4 py-2.5">Cancel</a>
    </div>
</form>
@endsection