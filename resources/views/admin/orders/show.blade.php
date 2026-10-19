@extends('layouts.admin')
@section('title', 'Order '.$order->reference.' — Admin')
@section('content')
<p class="text-xs text-muted"><a href="{{ route('admin.orders.index') }}" class="hover:text-forest">Orders</a> / {{ $order->reference }}</p>
<h1 class="font-display text-3xl tracking-tight mt-1">Order {{ $order->reference }}</h1>
<p class="text-sm mt-1">Status <span class="font-semibold capitalize">{{ str_replace('_',' ',$order->status) }}</span> · Payment {{ $order->payment_status }} · Placed {{ $order->placed_at?->format('d M Y, H:i') }}</p>
<div class="grid md:grid-cols-[1fr_320px] gap-4 mt-5">
<div class="bg-white border border-ink/10 rounded p-5">
<h2 class="font-medium">Items (snapshot — immune to later product edits)</h2>
<ul class="text-sm mt-3 space-y-2">
@foreach ($order->items as $it)
<li class="flex justify-between gap-2"><span>{{ $it->name }}{{ $it->variant_name ? ' ('.$it->variant_name.')' : '' }} × {{ $it->quantity }}<br><span class="text-xs text-muted">SKU {{ $it->sku }} · ৳{{ number_format((float)$it->unit_price,0) }} each</span></span><span>৳{{ number_format((float)$it->line_total,0) }}</span></li>
@endforeach
</ul>
<dl class="text-sm mt-3 pt-3 border-t border-ink/10 space-y-1">
<div class="flex justify-between"><dt class="text-muted">Subtotal</dt><dd>৳{{ number_format((float)$order->subtotal,0) }}</dd></div>
<div class="flex justify-between"><dt class="text-muted">Delivery</dt><dd>৳{{ number_format((float)$order->delivery_charge,0) }}</dd></div>
<div class="flex justify-between font-semibold"><dt>Total</dt><dd>৳{{ number_format((float)$order->total,0) }}</dd></div>
</dl>
<h2 class="font-medium mt-5">Deliver to</h2>
<p class="text-sm text-muted mt-1">{{ $order->customer_name }} · {{ $order->customer_phone }}<br>{{ $order->address_line1 }}<br><span class="capitalize">{{ $order->delivery_zone === 'outside_dhaka' ? 'Outside Dhaka' : 'Inside Dhaka' }}</span>@if(!empty($order->district) && !in_array($order->district, ['Dhaka', 'Outside Dhaka'], true)) · {{ $order->district }}@endif</p>
<h2 class="font-medium mt-5">Status history</h2>
<ul class="text-sm mt-2 space-y-1">
@foreach ($order->history as $h)
<li>{{ $h->created_at?->format('d M H:i') }} — {{ $h->from_status ?? '—' }} → <span class="font-medium">{{ str_replace('_',' ',$h->to_status) }}</span>@if($h->note) · {{ $h->note }}@endif</li>
@endforeach
</ul>
</div>
<aside class="bg-white border border-ink/10 rounded p-5 h-fit">
<h2 class="font-medium">Change status</h2>
<p class="text-xs text-muted mt-1">Only valid next steps are shown. This is logged with your name. Marking as <span class="font-medium">shipped</span> decreases inventory; cancelling a shipped order restores it.</p>
<form action="{{ route('admin.orders.status', $order) }}" method="POST" class="mt-3 space-y-3" onsubmit="return confirm('Change status to ' + document.getElementById('st').value + (document.getElementById('st').value === 'shipped' ? ' and DECREASE inventory?' : '?'));">
@csrf @method('PATCH')
<label for="st" class="text-sm font-medium">Next status</label>
<select id="st" name="status" class="w-full border border-ink/15 rounded px-3 py-2 text-sm bg-white">
@foreach (\App\Models\Order::TRANSITIONS[$order->status] ?? [] as $next)
<option value="{{ $next }}">{{ ucfirst(str_replace('_',' ',$next)) }}</option>
@endforeach
</select>
<label for="note" class="text-sm font-medium">Note (optional)</label>
<input id="note" name="note" maxlength="500" class="w-full border border-ink/15 rounded px-3 py-2 text-sm" placeholder="Reason for change">
@if (empty(\App\Models\Order::TRANSITIONS[$order->status]))
<p class="text-xs text-muted">Terminal state — no further transitions.</p>
@else
<button class="w-full bg-forest text-white text-sm font-medium px-4 py-2.5 rounded">Update status</button>
@endif
</form>
@if (auth()->user()?->canDeleteOrders())
<hr class="my-5 border-ink/10">
<h2 class="font-medium text-red-700">Delete order</h2>
<p class="text-xs text-muted mt-1">Permanent — removes the order, its items and history. If inventory was already deducted (shipped), it is restored first.</p>
<form action="{{ route('admin.orders.destroy', $order) }}" method="POST" class="mt-3" onsubmit="return confirm('Delete order {{ $order->reference }} permanently? This cannot be undone.');">
@csrf @method('DELETE')
<button class="w-full border border-red-300 text-red-700 bg-white hover:bg-red-50 text-sm font-medium px-4 py-2.5 rounded">Delete order</button>
</form>
@endif
</aside>
</div>
@endsection
