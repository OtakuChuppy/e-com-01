@extends('layouts.store')
@section('title', 'অর্ডার কনফার্ম — '.config('shop.name'))
@section('content')
<section class="max-w-2xl mx-auto px-4 sm:px-6 pt-12 md:pt-20 pb-12 text-center">
    <div class="w-16 h-16 rounded-full bg-moss text-forest flex items-center justify-center mx-auto mb-4 animate-scale-in">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 13l4 4L19 7"/></svg>
    </div>
    <h1 class="font-display text-3xl md:text-4xl tracking-tight mt-3">ধন্যবাদ — অর্ডার গৃহীত হয়েছে।</h1>
    <p class="text-muted mt-3">রেফারেন্স <span class="font-semibold text-ink">{{ $order->reference }}</span> · কনফার্ম করতে আমরা {{ $order->customer_phone }} নম্বরে কল করব।</p>
    <div class="mt-10 flex justify-center">
        <div class="text-left border border-ink/10 rounded-xl p-6 md:p-8 bg-white shadow-sm w-full max-w-md">
            <h2 class="font-medium mb-3">অর্ডার সারাংশ</h2>
            <ul class="text-sm mt-3 space-y-2">
                @foreach ($order->items as $it)
                <li class="flex justify-between gap-3"><span class="min-w-0">{{ $it->name }}{{ $it->variant_name ? ' ('.$it->variant_name.')' : '' }} × {{ $it->quantity }}</span><span class="shrink-0">৳{{ number_format((float) $it->line_total, 0) }}</span></li>
                @endforeach
            </ul>
            <dl class="text-sm mt-4 pt-4 border-t border-ink/10 space-y-1.5">
                <div class="flex justify-between gap-3"><dt class="text-muted">উপমোট</dt><dd class="shrink-0">৳{{ number_format((float) $order->subtotal, 0) }}</dd></div>
                <div class="flex justify-between gap-3"><dt class="text-muted">ডেলিভারি ({{ $order->delivery_zone === 'outside_dhaka' ? 'ঢাকার বাইরে' : 'ঢাকার ভিতরে' }})</dt><dd class="shrink-0">৳{{ number_format((float) $order->delivery_charge, 0) }}</dd></div>
                <div class="flex justify-between gap-3 font-semibold text-lg"><dt class="text-muted">মোট (COD)</dt><dd class="shrink-0">৳{{ number_format((float) $order->total, 0) }}</dd></div>
            </dl>
            <p class="text-xs text-muted mt-4">ডেলিভারি ঠিকানা: {{ $order->customer_name }}, {{ $order->address_line1 }}</p>
        </div>
    </div>
    <div class="flex flex-col sm:flex-row gap-3 justify-center mt-10">
        <a href="{{ route('products.index') }}" class="flex-1 border border-ink/20 text-sm font-medium px-4 py-3 rounded-xl text-ink hover:bg-lined transition-colors">কেনাকাটা চালিয়ে যান</a>
        <a href="{{ route('account.orders') }}" class="flex-1 btn btn-primary btn-md text-sm">অর্ডার ট্র্যাক করুন</a>
    </div>
</section>
@endsection
