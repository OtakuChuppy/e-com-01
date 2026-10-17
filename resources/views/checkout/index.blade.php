@extends('layouts.store')
@section('title', 'Checkout — '.config('shop.name'))
@section('content')
<section class="max-w-6xl mx-auto px-4 sm:px-6 pt-8" data-delivery-inside="{{ $deliveryInside }}" data-delivery-outside="{{ $deliveryOutside }}" data-subtotal="{{ $totals['subtotal'] }}">
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
            <div><label for="address_line1" class="text-sm font-medium">Full address *</label><input id="address_line1" name="address_line1" value="{{ old('address_line1') }}" required placeholder="House, road, area" class="mt-1 w-full border border-ink/15 rounded px-3 py-2.5 text-sm bg-white">@error('address_line1')<p class="text-xs text-red-700 mt-1">{{ $message }}</p>@enderror</div>
            <div>
                <span class="text-sm font-medium">Delivery zone *</span>
                <div class="mt-2 grid grid-cols-2 gap-3" role="radiogroup" aria-label="Delivery zone">
                    <label class="flex items-center gap-2 border border-ink/15 rounded px-3 py-2.5 text-sm bg-white cursor-pointer has-checked:border-forest has-checked:bg-moss">
                        <input type="radio" name="delivery_zone" value="inside_dhaka" @checked(old('delivery_zone', 'inside_dhaka') === 'inside_dhaka') class="accent-[#344B3F]">
                        <span>Inside Dhaka<br><span class="text-xs text-muted">৳{{ number_format($deliveryInside, 0) }}</span></span>
                    </label>
                    <label class="flex items-center gap-2 border border-ink/15 rounded px-3 py-2.5 text-sm bg-white cursor-pointer has-checked:border-forest has-checked:bg-moss">
                        <input type="radio" name="delivery_zone" value="outside_dhaka" @checked(old('delivery_zone') === 'outside_dhaka') class="accent-[#344B3F]">
                        <span>Outside Dhaka<br><span class="text-xs text-muted">৳{{ number_format($deliveryOutside, 0) }}</span></span>
                    </label>
                </div>
                @error('delivery_zone')<p class="text-xs text-red-700 mt-1">{{ $message }}</p>@enderror
            </div>
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
                <div class="flex justify-between"><dt class="text-muted">Delivery</dt><dd id="delivery-preview">—</dd></div>
                <div class="flex justify-between font-semibold text-base pt-1"><dt>Total</dt><dd id="total-preview">৳{{ number_format($totals['subtotal'], 0) }} + delivery</dd></div>
            </dl>
            <p class="text-xs text-muted mt-3">Inside Dhaka ৳{{ number_format($deliveryInside, 0) }} · Outside ৳{{ number_format($deliveryOutside, 0) }}</p>
        </aside>
    </div>
</section>
@push('scripts')
<script>
const cfg = document.querySelector('section[data-delivery-inside]').dataset;
const dIn = Number(cfg.deliveryInside);
const dOut = Number(cfg.deliveryOutside);
const sub = Number(cfg.subtotal);
const fmt = n => '৳' + n.toLocaleString('en-US', {maximumFractionDigits: 0});
const zoneInputs = document.querySelectorAll('input[name="delivery_zone"]');
function upd() {
    const checked = document.querySelector('input[name="delivery_zone"]:checked');
    const d = checked && checked.value === 'outside_dhaka' ? dOut : dIn;
    document.getElementById('delivery-preview').textContent = fmt(d);
    document.getElementById('total-preview').textContent = fmt(sub + d);
}
zoneInputs.forEach(i => i.addEventListener('change', upd)); upd();
document.getElementById('checkout-form').addEventListener('submit', (e) => {
    const b = document.getElementById('place-btn');
    if (b.disabled) e.preventDefault();
    b.disabled = true; b.textContent = 'Placing order…';
});
</script>
@endpush
@endsection
