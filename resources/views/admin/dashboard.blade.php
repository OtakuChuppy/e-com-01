@extends('layouts.admin')
@section('title', 'Dashboard — '.config('shop.name'))
@section('content')
<h1 class="font-display text-3xl tracking-tight">Dashboard</h1>
<p class="text-sm text-muted mt-1">Counts are live order statuses — new means awaiting confirmation.</p>
<div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3 mt-6">
<div class="bg-white border border-ink/10 rounded p-4"><p class="text-xs text-muted">Total orders</p><p class="text-2xl font-semibold mt-1">{{ $counts['total'] }}</p></div>
<div class="bg-white border border-ink/10 rounded p-4"><p class="text-xs text-muted">New (pending)</p><p class="text-2xl font-semibold mt-1">{{ $counts['new'] }}</p></div>
<div class="bg-white border border-ink/10 rounded p-4"><p class="text-xs text-muted">On hold</p><p class="text-2xl font-semibold mt-1">{{ $counts['on_hold'] }}</p></div>
<div class="bg-white border border-ink/10 rounded p-4"><p class="text-xs text-muted">Confirmed</p><p class="text-2xl font-semibold mt-1">{{ $counts['confirmed'] }}</p></div>
<div class="bg-white border border-ink/10 rounded p-4"><p class="text-xs text-muted">Delivered</p><p class="text-2xl font-semibold mt-1">{{ $counts['delivered'] }}</p></div>
<div class="bg-white border border-ink/10 rounded p-4"><p class="text-xs text-muted">Cancelled</p><p class="text-2xl font-semibold mt-1">{{ $counts['cancelled'] }}</p></div>
</div>
<div class="grid sm:grid-cols-2 gap-3 mt-4">
<div class="bg-white border border-ink/10 rounded p-4"><p class="text-xs text-muted">Sales, last 7 days (confirmed+)</p><p class="text-xl font-semibold mt-1">৳{{ number_format((float) $sales7, 0) }}</p></div>
<div class="bg-white border border-ink/10 rounded p-4"><p class="text-xs text-muted">Sales, last 30 days (confirmed+)</p><p class="text-xl font-semibold mt-1">৳{{ number_format((float) $sales30, 0) }}</p></div>
</div>
<h2 class="font-medium mt-8 mb-3">Recent orders</h2>
@if ($recent->isEmpty())
<p class="text-sm text-muted border border-dashed border-ink/20 rounded p-6 text-center">No orders yet. New checkout orders will appear here.</p>
@else
<div class="bg-white border border-ink/10 rounded overflow-x-auto">
<table class="w-full text-sm min-w-140">
<thead><tr class="text-left text-xs text-muted border-b border-ink/10"><th class="px-4 py-2">Reference</th><th class="px-4 py-2">Customer</th><th class="px-4 py-2">Total</th><th class="px-4 py-2">Status</th><th class="px-4 py-2">Placed</th></tr></thead>
<tbody>
@foreach ($recent as $o)
<tr class="border-b border-ink/5 last:border-0">
<td class="px-4 py-2"><a class="text-forest underline underline-offset-4" href="{{ route('admin.orders.show', $o) }}">{{ $o->reference }}</a></td>
<td class="px-4 py-2">{{ $o->customer_name }}</td>
<td class="px-4 py-2">৳{{ number_format((float) $o->total, 0) }}</td>
<td class="px-4 py-2 capitalize">{{ str_replace('_', ' ', $o->status) }}</td>
<td class="px-4 py-2 text-muted">{{ $o->placed_at?->diffForHumans() }}</td>
</tr>
@endforeach
</tbody></table></div>
@endif
@endsection
