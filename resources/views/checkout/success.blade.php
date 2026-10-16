@extends('layouts.store')
@section('title', 'Order confirmed — '.config('shop.name'))
@section('content')
<section class="max-w-2xl mx-auto px-4 sm:px-6 pt-12 text-center">
    <p class="w-12 h-12 rounded-full bg-moss text-forest grid place-items-center mx-auto" aria-hidden="true"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 13l4 4L19 7"/></svg></p>
    <h1 class="font-display text-3xl md:text-4xl tracking-tight mt-4">Thank you — order received.</h1>
    <p class="text-muted mt-2">Reference <span class="font-semibold text-ink">{{ $order->reference }}</span> · We will call {{ $order->customer_phone }} to confirm.</p>
    <div class="text-left border border-ink/10 rounded p-5 mt-8 bg-white/60">
        <h2 class="font-medium">Order summary</h2>
        <ul class="text-sm mt-3 space-y-1.5">
            @foreach ($order->items as $it)
            <li class="flex justify-between gap-2"><span>{{ $it->name }}{{ $it->variant_name ? ' ('.$it->variant_name.')' : '' }} × {{ $it->quantity }}</span><span>৳{{ number_format((float) $it->line_total, 0) }}</span></li>
            @endforeach
        </ul>
        <dl class="text-sm mt-3 pt-3 border-t border-ink/10 space-y-1">
            <div class="flex justify-between"><dt class="text-muted">Subtotal</dt><dd>৳{{ number_format((float) $order->subtotal, 0) }}</dd></div>
            <div class="flex justify-between"><dt class="text-muted">Delivery ({{ $order->district }})</dt><dd>৳{{ number_format((float) $order->delivery_charge, 0) }}</dd></div>
            <div class="flex justify-between font-semibold text-base"><dt>Total (COD)</dt><dd>৳{{ number_format((float) $order->total, 0) }}</dd></div>
        </dl>
        <p class="text-xs text-muted mt-3">Deliver to: {{ $order->address_line1 }}, {{ $order->district }}</p>
    </div>
    <div class="flex gap-3 mt-6">
        <a href="{{ route('products.index') }}" class="flex-1 border border-ink/20 text-sm font-medium px-4 py-3 rounded">Continue shopping</a>
        <a href="{{ route('account.orders') }}" class="flex-1 bg-forest text-white text-sm font-medium px-4 py-3 rounded">Track order</a>
    </div>
</section>
@endsection
