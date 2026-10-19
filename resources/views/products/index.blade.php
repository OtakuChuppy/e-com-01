@extends('layouts.store')
@section('title', ($activeCategory?->name ?? 'সব পণ্য').' — '.config('shop.name'))
@section('content')
<section class="max-w-7xl mx-auto px-4 sm:px-6 pt-8 md:pt-12">
    <div class="flex gap-8">
        <aside class="hidden md:block w-56 shrink-0">
            <div class="sticky top-24">
                <h2 class="text-xs font-semibold uppercase tracking-[0.14em] text-muted mb-3 px-1">ক্যাটাগরি</h2>
                <nav class="space-y-0.5" aria-label="ক্যাটাগরি">
                    <a href="{{ route('products.index', array_filter(['q' => request('q'), 'sort' => request('sort')])) }}" class="block px-3 py-2 rounded-lg text-sm transition-colors {{ $activeCategory ? 'text-muted hover:bg-linen hover:text-forest' : 'bg-moss text-forest font-medium' }}">সব পণ্য</a>
                    @foreach ($categories as $c)
                    <a href="{{ route('products.index', array_filter(['category' => $c->slug, 'q' => request('q'), 'sort' => request('sort')])) }}" class="block px-3 py-2 rounded-lg text-sm transition-colors {{ $activeCategory?->slug === $c->slug ? 'bg-moss text-forest font-medium' : 'text-muted hover:bg-linen hover:text-forest' }}">{{ $c->name }}</a>
                    @endforeach
                </nav>
            </div>
        </aside>
        <div class="flex-1 min-w-0">
            <div class="section-header">
                <div>
                    <p class="text-xs text-muted"><a href="{{ route('home') }}" class="hover:text-forest transition-colors">হোম</a> / {{ $activeCategory?->name ?? 'সব পণ্য' }}</p>
                    <h1 class="section-title">{{ $activeCategory?->name ?? 'সব পণ্য' }}</h1>
                    <p class="section-subtitle">{{ $products->total() }}টি পণ্য · ডেমো ক্যাটালগ</p>
                </div>
            </div>
            <form method="GET" class="flex flex-wrap gap-2 md:gap-3 items-center mb-8 border border-ink/10 rounded-xl p-4 sm:p-6 bg-white shadow-sm" role="search">
                <div class="flex-1 min-w-50">
                    <label for="q" class="sr-only">খুঁজুন</label>
                    <input id="q" name="q" value="{{ request('q') }}" placeholder="পণ্য খুঁজুন…" class="w-full border border-ink/15 rounded-lg px-4 py-3 text-sm bg-white focus:border-forest focus:ring-1 focus:ring-forest outline-none">
                </div>
                <div class="min-w-45 md:hidden">
                    <label for="category" class="sr-only">ক্যাটাগরি</label>
                    <select id="category" name="category" class="w-full border border-ink/15 rounded-lg px-4 py-3 text-sm bg-white focus:border-forest focus:ring-1 focus:ring-forest outline-none cursor-pointer">
                        <option value="">সব ক্যাটাগরি</option>
                        @foreach ($categories as $c)
                        <option value="{{ $c->slug }}" @selected(request('category') === $c->slug)>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="min-w-45">
                    <label for="sort" class="sr-only">সাজান</label>
                    <select id="sort" name="sort" class="w-full border border-ink/15 rounded-lg px-4 py-3 text-sm bg-white focus:border-forest focus:ring-1 focus:ring-forest outline-none cursor-pointer">
                        <option value="newest" @selected(request('sort', 'newest') === 'newest')>নতুন আগে</option>
                        <option value="price_asc" @selected(request('sort') === 'price_asc')>দাম: কম থেকে বেশি</option>
                        <option value="price_desc" @selected(request('sort') === 'price_desc')>দাম: বেশি থেকে কম</option>
                        <option value="name" @selected(request('sort') === 'name')>নাম (A–Z)</option>
                    </select>
                </div>
                <button class="btn btn-primary btn-md whitespace-nowrap">প্রয়োগ</button>
                @if (request()->hasAny(['q', 'category', 'sort']))
                <a href="{{ route('products.index') }}" class="text-sm text-muted underline underline-offset-4 hover:text-forest transition-colors ml-3">ফিল্টার মুছুন</a>
                @endif
            </form>
            @if ($products->isEmpty())
            <div class="empty-state mt-6 animate-fade-in">
                <p class="empty-state-title">কোনো পণ্য পাওয়া যায়নি।</p>
                <p class="empty-state-desc">অন্য সার্চ বা ক্যাটাগরি চেষ্টা করুন।</p>
                <a href="{{ route('products.index') }}" class="mt-6 btn btn-primary btn-md">সব ফিল্টার মুছুন</a>
            </div>
            @else
            <div class="grid grid-cols-2 lg:grid-cols-3 gap-x-5 gap-y-10">
                @foreach ($products as $product)
                @include('components.product-card', ['product' => $product])
                @endforeach
            </div>
            <nav class="mt-12" aria-label="পেজিনেশন">{{ $products->links() }}</nav>
            @endif
        </div>
    </div>
</section>
@endsection
