@extends('layouts.store')
@section('title', config('shop.name').' — Everyday essentials for Bangladesh')
@section('content')
<section class="max-w-7xl mx-auto px-4 sm:px-6 pt-10 md:pt-16">
    <div class="grid md:grid-cols-2 gap-8 md:gap-12 items-center">
        <div>
            <p class="text-xs tracking-[0.18em] uppercase text-clay font-medium">New collection · Demo preview</p>
            <h1 class="font-display text-4xl sm:text-5xl lg:text-[3.4rem] leading-[1.08] tracking-tight mt-3">Honest essentials<br>for everyday living.</h1>
            <p class="text-muted text-[17px] leading-relaxed mt-4 max-w-md">A curated shelf of daily goods — kitchen, home, personal care and more. Fair prices in Taka, Cash on Delivery, delivery across Bangladesh.</p>
            <div class="flex flex-wrap gap-3 mt-7">
                <a href="{{ route('products.index') }}" class="bg-forest text-white text-[15px] font-medium px-6 py-3 rounded">Shop all products</a>
                <a href="#featured" class="border border-ink/20 text-[15px] font-medium px-6 py-3 rounded hover:border-forest hover:text-forest">Featured picks</a>
            </div>
            <dl class="flex gap-8 mt-8 text-sm">
                <div><dt class="text-muted">Delivery</dt><dd class="font-semibold mt-0.5">Inside Dhaka ৳60</dd></div>
                <div><dt class="text-muted">Outside Dhaka</dt><dd class="font-semibold mt-0.5">৳120</dd></div>
                <div><dt class="text-muted">Payment</dt><dd class="font-semibold mt-0.5">Cash on Delivery</dd></div>
            </dl>
        </div>
        <div class="grid grid-cols-2 gap-3 md:gap-4">
            <img src="https://images.unsplash.com/photo-1556909212-d5b604d0c90d?w=700&q=80&auto=format&fit=crop" alt="Curated kitchen essentials" class="rounded aspect-[3/4] object-cover w-full" width="700" height="933" fetchpriority="high" onerror="this.style.display='none'">
            <img src="https://images.unsplash.com/photo-1523381210434-271e8be1f52b?w=700&q=80&auto=format&fit=crop" alt="Folded everyday apparel" class="rounded aspect-[3/4] object-cover w-full mt-6" width="700" height="933" loading="lazy" onerror="this.style.display='none'">
        </div>
    </div>
</section>
<section class="max-w-7xl mx-auto px-4 sm:px-6 mt-16 md:mt-20" aria-labelledby="cats">
    <div class="flex items-end justify-between">
        <div>
            <h2 id="cats" class="font-display text-2xl md:text-3xl tracking-tight">Shop by category</h2>
            <p class="text-muted text-sm mt-1">Six shelves to start — more as the catalogue grows.</p>
        </div>
        <a href="{{ route('products.index') }}" class="text-sm text-forest underline underline-offset-4 hidden sm:inline">View all</a>
    </div>
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4 mt-6">
        @foreach ($categories as $cat)
        <a href="{{ route('products.index', ['category' => $cat->slug]) }}" class="group">
            <span class="block rounded bg-linen aspect-square overflow-hidden">
                <img src="https://images.unsplash.com/photo-1441986300917-64674bd600d8?w=400&q=70&auto=format&fit=crop" alt="{{ $cat->name }}" loading="lazy" width="400" height="400" class="w-full h-full object-cover transition duration-300 group-hover:scale-105" onerror="this.style.display='none'">
            </span>
            <span class="block text-sm font-medium mt-2 group-hover:text-forest">{{ $cat->name }}</span>
        </a>
        @endforeach
    </div>
</section>
<section id="featured" class="max-w-7xl mx-auto px-4 sm:px-6 mt-16 md:mt-20" aria-labelledby="feat">
    <div class="flex items-end justify-between">
        <div>
            <h2 id="feat" class="font-display text-2xl md:text-3xl tracking-tight">Featured products</h2>
            <p class="text-muted text-sm mt-1">Hand-picked for the demo presentation.</p>
        </div>
        <a href="{{ route('products.index') }}" class="text-sm text-forest underline underline-offset-4">View all products</a>
    </div>
    @if ($featured->isEmpty())
    <p class="text-muted text-sm mt-6 border border-dashed border-ink/20 rounded p-6 text-center">Products are being added — check back soon.</p>
    @else
    <div class="grid grid-cols-2 md:grid-cols-4 gap-x-4 gap-y-8 mt-6">
        @foreach ($featured as $product)
        @include('components.product-card', ['product' => $product])
        @endforeach
    </div>
    @endif
</section>
<section class="max-w-7xl mx-auto px-4 sm:px-6 mt-16 md:mt-20">
    <div class="grid md:grid-cols-2 rounded overflow-hidden bg-forest text-white">
        <div class="p-8 md:p-12">
            <p class="text-xs tracking-[0.18em] uppercase opacity-70">Cash on Delivery</p>
            <h2 class="font-display text-2xl md:text-3xl mt-2 leading-tight">Order online, pay at your door.</h2>
            <p class="text-sm opacity-80 mt-3 max-w-sm leading-relaxed">No advance payment needed. Receive anywhere in Bangladesh, pay in cash when your parcel arrives.</p>
            <a href="{{ route('products.index') }}" class="inline-block mt-6 bg-white text-forest text-sm font-medium px-5 py-2.5 rounded">Start shopping</a>
        </div>
        <img src="https://images.unsplash.com/photo-1586880244406-556ebe35f282?w=900&q=80&auto=format&fit=crop" alt="Parcel ready for delivery" class="h-56 md:h-auto w-full object-cover" loading="lazy" onerror="this.style.display='none'">
    </div>
</section>
<section class="max-w-7xl mx-auto px-4 sm:px-6 mt-16 md:mt-20" aria-labelledby="latest">
    <h2 id="latest" class="font-display text-2xl md:text-3xl tracking-tight">Just arrived</h2>
    @if ($latest->isEmpty())
    <p class="text-muted text-sm mt-6 border border-dashed border-ink/20 rounded p-6 text-center">New arrivals will appear here.</p>
    @else
    <div class="grid grid-cols-2 md:grid-cols-4 gap-x-4 gap-y-8 mt-6">
        @foreach ($latest as $product)
        @include('components.product-card', ['product' => $product])
        @endforeach
    </div>
    @endif
    <div class="text-center mt-10">
        <a href="{{ route('products.index') }}" class="inline-block border border-forest text-forest font-medium px-8 py-3 rounded text-[15px]">View all products</a>
    </div>
</section>
<section class="max-w-7xl mx-auto px-4 sm:px-6 mt-16 md:mt-20 pb-4" aria-label="How ordering works">
    <div class="grid sm:grid-cols-3 gap-6 border-t border-ink/10 pt-8 text-sm">
        <div><p class="font-medium">Cash on Delivery</p><p class="text-muted mt-1">Pay in cash when your order arrives. No card required.</p></div>
        <div><p class="font-medium">Dhaka & nationwide shipping</p><p class="text-muted mt-1">৳60 inside Dhaka, ৳120 outside. Rates shown before you pay.</p></div>
        <div><p class="font-medium">Order lookup</p><p class="text-muted mt-1">Track any order with its reference and phone number.</p></div>
    </div>
</section>
@endsection
