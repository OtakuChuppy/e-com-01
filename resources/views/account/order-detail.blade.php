@extends('layouts.store')
@section('title', 'Order '.$order->reference.' — '.config('shop.name'))
@section('content')
<section class="max-w-2xl mx-auto px-4 sm:px-6 pt-8">
    <p class="text-xs text-muted"><a href="{{ route('account.orders') }}" class="hover:text-forest">Order lookup</a> / {{ $order->reference }}</p>
    <h1 class="font-display text-3xl tracking-tight mt-1">Order {{ $order->reference }}</h1>
    <p class="text-sm mt-1">Status: <span class="font-semibold capitalize">{{ str_replace('_', ' ', $order->status) }}</span> · Payment: {{ $order->payment_status === 'paid' ? 'Paid' : 'Cash on Delivery (unpaid)' }}</p>
    <div class="border border-ink/10 rounded p-5 mt-5 bg-white/60">
        <ul class="text-sm space-y-1.5">
            @foreach ($order->items as $it)
            <li class="flex justify-between gap-2"><span>{{ $it->name }}{{ $it->variant_name ? ' ('.$it->variant_name.')' : '' }} × {{ $it->quantity }}</span><span>৳{{ number_format((float) $it->line_total, 0) }}</span></li>
            @endforeach
        </ul>
        <dl class="text-sm mt-3 pt-3 border-t border-ink/10 space-y-1">
            <div class="flex justify-between"><dt class="text-muted">Subtotal</dt><dd>৳{{ number_format((float) $order->subtotal, 0) }}</dd></div>
            <div class="flex justify-between"><dt class="text-muted">Delivery</dt><dd>৳{{ number_format((float) $order->delivery_charge, 0) }}</dd></div>
            <div class="flex justify-between font-semibold text-base"><dt>Total</dt><dd>৳{{ number_format((float) $order->total, 0) }}</dd></div>
        </dl>
        <p class="text-xs text-muted mt-3">Deliver to: {{ $order->customer_name }}, {{ $order->address_line1 }}, {{ $order->district }} · {{ $order->customer_phone }}</p>
    </div>
</section>
@endsection
