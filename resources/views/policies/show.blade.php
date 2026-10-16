@extends('layouts.store')
@section('title', ucfirst($page).' policy — '.config('shop.name'))
@section('content')
<section class="max-w-3xl mx-auto px-4 sm:px-6 pt-8">
    @if ($page === 'shipping')
    <h1 class="font-display text-3xl tracking-tight">Shipping info</h1>
    <div class="text-[15px] leading-relaxed mt-4 space-y-3 text-ink/80">
        <p>We deliver across Bangladesh with Cash on Delivery. Delivery charge is ৳60 inside Dhaka and ৳120 outside Dhaka, shown before you confirm your order.</p>
        <p>Orders are confirmed by phone. Delivery time depends on your district and courier capacity — we will share updates when your parcel ships.</p>
    </div>
    @elseif ($page === 'returns')
    <h1 class="font-display text-3xl tracking-tight">Returns &amp; cancellations</h1>
    <div class="text-[15px] leading-relaxed mt-4 space-y-3 text-ink/80">
        <p>You may cancel an order before it ships by contacting us with your order reference and phone number.</p>
        <p>If an item arrives damaged or wrong, contact us within 48 hours with photos. We will arrange a replacement or refund after review.</p>
    </div>
    @else
    <h1 class="font-display text-3xl tracking-tight">Privacy</h1>
    <div class="text-[15px] leading-relaxed mt-4 space-y-3 text-ink/80">
        <p>We collect only what is needed to fulfil your order: name, phone, delivery address, and order details. We never sell your information.</p>
        <p>Order lookup requires both your reference and phone number. Analytics, if enabled, never receives your personal details.</p>
    </div>
    @endif
</section>
@endsection
