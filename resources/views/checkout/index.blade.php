@extends('layouts.store')
@section('title', 'Checkout — '.config('shop.name'))
@section('content')
<section class="max-w-6xl mx-auto px-4 sm:px-6 pt-8">
    <h1 class="font-display text-3xl tracking-tight">Checkout</h1>
    <p class="text-sm text-muted mt-1">Cash on Delivery · Pay when your parcel arrives.</p>
    <div class="grid md:grid-cols-[1fr_360px] gap-8 mt-6">
        <form action="{{ route('checkout.store') }}" method="POST" class="space-y-4" id="checkout-form">
            @csrf
            <input type="hidden" name="idempotency_key" value="{{ \Illuminate\Support\Str::uuid() }}">
            <div class="grid sm:grid-cols-2 gap-4">
                <div><label for="name" class="text-sm font-medium">Full name *</label><input id="name" name="name" value="{{ old('name') }}" required class="mt-1 w-full border border-ink/15 rounded px-3 py-2.5 text-sm bg-white">@error('name')<p class="text-xs text-red-700 mt-1">{{ $message }}</p>@enderror</div>
                <div><label for="phone" class="text-sm font-medium">Phone *</label><input id="phone" name="phone" value="{{ old('phone') }}" required placeholder="01XXXXXXXXX" class="mt-1 w-full border border-ink/15 rounded px-3 py-2.5 text-sm bg-white">@error('phone')<p class="text-xs text-red-700 mt-1">{{ $message }}</p>@enderror</div>
            </div>
            <div><label for="email" class="text-sm font-medium">Email (optional)</label><input id="email" name="email" type="email" value="{{ old('email') }}" class="mt-1 w-full border border-ink/15 rounded px-3 py-2.5 text-sm bg-white"></div>
            <div><label for="address_line1" class="text-sm font-medium">Address *</label><input id="address_line1" name="address_line1" value="{{ old('address_line1') }}" required placeholder="House, road, area" class="mt-1 w-full border border-ink/15 rounded px-3 py-2.5 text-sm bg-white">@error('address_line1')<p class="text-xs text-red-700 mt-1">{{ $message }}</p>@enderror</div>
            <div class="grid sm:grid-cols-3 gap-4">
                <div><label for="area" class="text-sm font-medium">Area</label><input id="area" name="area" value="{{ old('area') }}" class="mt-1 w-full border border-ink/15 rounded px-3 py-2.5 text-sm bg-white"></div>
                <div><label for="district" class="text-sm font-medium">District *</label><input id="district" name="district" value="{{ old('district', 'Dhaka') }}" required list="districts" class="mt-1 w-full border border-ink/15 rounded px-3 py-2.5 text-sm bg-white"><datalist id="districts"><option value="Dhaka"></option><option value="Chattogram"></option><option value="Sylhet"></option><option value="Rajshahi"></option><option value="Khulna"></option><option value="Barishal"></option><option value="Rangpur"></option><option value="Mymensingh"></option></datalist>@error('district')<p class="text-xs text-red-700 mt-1">{{ $message }}</p>@enderror</div>
                <div><label for="postal_code" class="text-sm font-medium">Postal code</label><input id="postal_code" name="postal_code" value="{{ old('postal_code') }}" class="mt-1 w-full border border-ink/15 rounded px-3 py-2.5 text-sm bg-white"></div>
            </div>
            <div><label for="delivery_notes" class="text-sm font-medium">Delivery instructions (optional)</label><textarea id="delivery_notes" name="delivery_notes" rows="2" class="mt-1 w-full border border-ink/15 rounded px-3 py-2.5 text-sm bg-white">{{ old('delivery_notes') }}</textarea></div>
            <button id="place-btn" class="w-full bg-forest text-white font-medium px-6 py-3 rounded">Place order · Cash on Delivery</button>
            <p class="text-xs text-muted">Prices and stock are rechecked on our server when you place the order. Double-clicking is safe — duplicates are blocked.</p>
        </form>
        <aside class="border border-ink/10 rounded p-5 h-fit bg-white/60" aria-label="Order summary">
            <h2 class="font-medium">Order summary</h2>
            <ul class="text-sm mt-3 space-y-2">
                @foreach ($cart->items as $item)
                @php $price = (float) ($item->variant?->price_override ?? $item->product->base_price); @endphp
                <li class="flex justify-between gap-2"><span>{{ $item->product->title }} × {{ $item->quantity }}</span><span>৳{{ number_format($price * $item->quantity, 0) }}</span></li>
                @endforeach
            </ul>
            <dl class="text-sm mt-4 pt-4 border-t border-ink/10 space-y-1.5">
                <div class="flex justify-between"><dt class="text-muted">Subtotal</dt><dd>৳{{ number_format($totals['subtotal'], 0) }}</dd></div>
                <div class="flex justify-between"><dt class="text-muted">Delivery</dt><dd id="delivery-preview">calculated by district</dd></div>
                <div class="flex justify-between font-semibold text-base pt-1"><dt>Total</dt><dd id="total-preview">৳{{ number_format($totals['subtotal'], 0) }} + delivery</dd></div>
            </dl>
            <p class="text-xs text-muted mt-3">Inside Dhaka ৳{{ number_format($deliveryInside, 0) }} · Outside ৳{{ number_format($deliveryOutside, 0) }}</p>
        </aside>
    </div>
</section>
@push('scripts')
<script>
const dIn = {{ $deliveryInside }}, dOut = {{ $deliveryOutside }}, sub = {{ $totals['subtotal'] }};
const fmt = n => '৳' + n.toLocaleString('en-US', {maximumFractionDigits: 0});
const dist = document.getElementById('district');
function upd() {
    const inside = (dist.value || '').trim().toLowerCase() === 'dhaka';
    const d = inside ? dIn : dOut;
    document.getElementById('delivery-preview').textContent = fmt(d);
    document.getElementById('total-preview').textContent = fmt(sub + d);
}
dist.addEventListener('input', upd); upd();
document.getElementById('checkout-form').addEventListener('submit', (e) => {
    const b = document.getElementById('place-btn');
    if (b.disabled) e.preventDefault();
    b.disabled = true; b.textContent = 'Placing order…';
});
</script>
@endpush
@endsection
