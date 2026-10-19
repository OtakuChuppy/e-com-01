@extends('layouts.store')
@section('title', 'Order '.$order->reference.' — '.config('shop.name'))
@section('content')
<section class="max-w-2xl mx-auto px-4 sm:px-6 pt-8 md:pt-12">
    <p class="text-xs text-muted"><a href="{{ route('account.orders') }}" class="hover:text-forest transition-colors">অর্ডার লুকআপ</a> / {{ $order->reference }}</p>
    <h1 class="font-display text-3xl tracking-tight mt-1">অর্ডার {{ $order->reference }}</h1>
    <p class="text-sm mt-2">
        স্ট্যাটাস: <span class="font-semibold capitalize">{{ ['pending' => 'অপেক্ষমাণ', 'confirmed' => 'কনফার্ম', 'processing' => 'প্রক্রিয়াধীন', 'shipped' => 'পাঠানো হয়েছে', 'delivered' => 'ডেলিভার হয়েছে', 'cancelled' => 'বাতিল', 'returned' => 'ফেরত'][$order->status] ?? ucfirst(str_replace('_', ' ', $order->status)) }}</span>
        @php $badge = $order->status === 'delivered' ? 'badge-forest' : ($order->status === 'cancelled' ? 'badge-outline' : 'badge-moss'); @endphp
        <span class="badge {{ $badge }} ml-3">{{ ['pending' => 'অপেক্ষমাণ', 'confirmed' => 'কনফার্ম', 'processing' => 'প্রক্রিয়াধীন', 'shipped' => 'পাঠানো হয়েছে', 'delivered' => 'ডেলিভার হয়েছে', 'cancelled' => 'বাতিল', 'returned' => 'ফেরত'][$order->status] ?? ucfirst(str_replace('_', ' ', $order->status)) }}</span>
        <span class="text-muted ml-2">· পেমেন্ট: {{ $order->payment_status === 'paid' ? 'পরিশোধিত' : 'ক্যাশ অন ডেলিভারি (বাকি)' }}</span>
    </p>
    <div class="mt-6 card card-sm p-6 md:p-8">
        <ul class="text-sm space-y-1.5 mb-6">
            @foreach ($order->items as $it)
            <li class="flex justify-between gap-2"><span>{{ $it->name }}{{ $it->variant_name ? ' ('.$it->variant_name.')' : '' }} × {{ $it->quantity }}</span><span>৳{{ number_format((float) $it->line_total, 0) }}</span></li>
            @endforeach
        </ul>
        <dl class="text-sm mt-3 pt-4 border-t border-ink/10 space-y-1.5">
            <div class="flex justify-between"><dt class="text-muted">উপমোট</dt><dd>৳{{ number_format((float) $order->subtotal, 0) }}</dd></div>
            <div class="flex justify-between"><dt class="text-muted">ডেলিভারি</dt><dd>৳{{ number_format((float) $order->delivery_charge, 0) }}</dd></div>
            <div class="flex justify-between font-semibold text-lg"><dt>মোট</dt><dd>৳{{ number_format((float) $order->total, 0) }}</dd></div>
        </dl>
        <p class="text-xs text-muted mt-4">ডেলিভারি ঠিকানা: {{ $order->customer_name }}, {{ $order->address_line1 }} · {{ $order->customer_phone }} · {{ $order->delivery_zone === 'outside_dhaka' ? 'ঢাকার বাইরে' : 'ঢাকার ভিতরে' }}</p>
    </div>
</section>
@endsection
