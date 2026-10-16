@extends('layouts.store')
@section('title', 'Your cart — '.config('shop.name'))
@section('content')
<section class="max-w-4xl mx-auto px-4 sm:px-6 pt-8">
    <h1 class="font-display text-3xl tracking-tight">Your cart</h1>
    @if ($cart->items->isEmpty())
    <div class="border border-dashed border-ink/20 rounded p-10 text-center mt-6">
        <p class="font-medium">Your cart is empty.</p>
        <a href="{{ route('products.index') }}" class="inline-block mt-3 bg-forest text-white text-sm px-5 py-2.5 rounded">Browse products</a>
    </div>
    @else
    <ul class="divide-y divide-ink/10 mt-6">
        @foreach ($cart->items as $item)
        @php $p = $item->product; $price = (float) ($item->variant?->price_override ?? $p->base_price); @endphp
        <li class="py-4 flex gap-4">
            <div class="w-20 h-24 bg-linen rounded overflow-hidden shrink-0">
                @php $im = $p->primaryImage(); $s = $im?->path; if ($s && ! str_starts_with($s, 'http')) $s = asset('storage/'.$s); @endphp
                <img src="{{ $s ?? 'https://images.unsplash.com/photo-1441986300917-64674bd600d8?w=200&q=60&auto=format&fit=crop' }}" alt="{{ $p->title }}" class="w-full h-full object-cover" loading="lazy">
            </div>
            <div class="flex-1">
                <a href="{{ route('products.show', $p->slug) }}" class="font-medium hover:text-forest">{{ $p->title }}</a>
                @if ($item->variant)<p class="text-xs text-muted mt-0.5">{{ $item->variant->name }}</p>@endif
                <p class="text-sm mt-1">৳{{ number_format($price, 0) }} each</p>
                <div class="flex items-center gap-3 mt-2">
                    <form action="{{ route('cart.update', $item) }}" method="POST" class="flex items-center gap-2">
                        @csrf @method('PATCH')
                        <label class="sr-only" for="q{{ $item->id }}">Quantity</label>
                        <input id="q{{ $item->id }}" name="quantity" type="number" min="1" max="20" value="{{ $item->quantity }}" class="w-16 border border-ink/15 rounded px-2 py-1 text-sm text-center">
                        <button class="text-sm text-forest underline underline-offset-4">Update</button>
                    </form>
                    <form action="{{ route('cart.destroy', $item) }}" method="POST" onsubmit="return confirm('Remove this item?');">
                        @csrf @method('DELETE')
                        <button class="text-sm text-muted underline underline-offset-4">Remove</button>
                    </form>
                </div>
            </div>
            <p class="font-semibold text-[15px]">৳{{ number_format($price * $item->quantity, 0) }}</p>
        </li>
        @endforeach
    </ul>
    <div class="flex justify-between items-center border-t border-ink/10 pt-5 mt-2">
        <p class="text-muted text-sm">{{ $totals['count'] }} item(s)</p>
        <p class="text-lg">Subtotal <span class="font-semibold">৳{{ number_format($totals['subtotal'], 0) }}</span></p>
    </div>
    <p class="text-xs text-muted mt-1 text-right">Delivery calculated at checkout (৳60 Dhaka / ৳120 outside).</p>
    <div class="flex gap-3 mt-5">
        <a href="{{ route('products.index') }}" class="flex-1 text-center border border-ink/20 text-sm font-medium px-4 py-3 rounded">Continue shopping</a>
        <a href="{{ route('checkout.index') }}" class="flex-1 text-center bg-forest text-white text-sm font-medium px-4 py-3 rounded">Checkout</a>
    </div>
    @endif
</section>
@endsection
