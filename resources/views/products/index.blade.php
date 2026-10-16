@extends('layouts.store')
@section('title', ($activeCategory?->name ?? 'All products').' — '.config('shop.name'))
@section('content')
<section class="max-w-7xl mx-auto px-4 sm:px-6 pt-8">
    <p class="text-xs text-muted"><a href="{{ route('home') }}" class="hover:text-forest">Home</a> / {{ $activeCategory?->name ?? 'All products' }}</p>
    <h1 class="font-display text-3xl md:text-4xl tracking-tight mt-2">{{ $activeCategory?->name ?? 'All products' }}</h1>
    <p class="text-muted text-sm mt-1">{{ $products->total() }} item{{ $products->total() === 1 ? '' : 's' }} · Demo catalogue</p>
    <form method="GET" class="flex flex-wrap gap-2 mt-5 items-center" role="search">
        <label class="sr-only" for="q">Search</label>
        <input id="q" name="q" value="{{ request('q') }}" placeholder="Search products…" class="border border-ink/15 rounded px-3 py-2 text-sm bg-white w-52">
        <label class="sr-only" for="category">Category</label>
        <select id="category" name="category" class="border border-ink/15 rounded px-3 py-2 text-sm bg-white">
            <option value="">All categories</option>
            @foreach ($categories as $c)
            <option value="{{ $c->slug }}" @selected(request('category') === $c->slug)>{{ $c->name }}</option>
            @endforeach
        </select>
        <label class="sr-only" for="sort">Sort</label>
        <select id="sort" name="sort" class="border border-ink/15 rounded px-3 py-2 text-sm bg-white">
            <option value="newest" @selected(request('sort', 'newest') === 'newest')>Newest</option>
            <option value="price_asc" @selected(request('sort') === 'price_asc')>Price: low to high</option>
            <option value="price_desc" @selected(request('sort') === 'price_desc')>Price: high to low</option>
            <option value="name" @selected(request('sort') === 'name')>Name A–Z</option>
        </select>
        <button class="bg-forest text-white text-sm px-4 py-2 rounded">Apply</button>
        @if (request()->hasAny(['q', 'category', 'sort']))
        <a href="{{ route('products.index') }}" class="text-sm text-muted underline underline-offset-4">Clear</a>
        @endif
    </form>
    @if ($products->isEmpty())
    <div class="border border-dashed border-ink/20 rounded p-10 text-center mt-6">
        <p class="font-medium">No products found.</p>
        <p class="text-sm text-muted mt-1">Try a different search or category.</p>
    </div>
    @else
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-x-4 gap-y-8 mt-6">
        @foreach ($products as $product)
        @include('components.product-card', ['product' => $product])
        @endforeach
    </div>
    <nav class="mt-10" aria-label="Pagination">{{ $products->links() }}</nav>
    @endif
</section>
@endsection
