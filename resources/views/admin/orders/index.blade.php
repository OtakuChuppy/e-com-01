@extends('layouts.admin')
@section('title', 'Orders — Admin')
@section('content')
<h1 class="font-display text-3xl tracking-tight">Orders</h1>
<form method="GET" class="flex flex-wrap gap-2 mt-4">
    <input name="q" value="{{ request('q') }}" placeholder="Reference, name, phone…" class="border border-ink/15 rounded px-3 py-2 text-sm bg-white w-60">
    <select name="status" class="border border-ink/15 rounded px-3 py-2 text-sm bg-white">
        <option value="">All statuses</option>
        @foreach (['pending','confirmed','on_hold','shipped','delivered','cancelled'] as $s)
        <option value="{{ $s }}" @selected(request('status')===$s)>{{ ucfirst(str_replace('_',' ',$s)) }}</option>
        @endforeach
    </select>
    <button class="bg-forest text-white text-sm px-4 py-2 rounded">Filter</button>
</form>
@if ($orders->isEmpty())
<p class="text-sm text-muted border border-dashed border-ink/20 rounded p-6 text-center mt-4">No orders match. Try clearing the filter.</p>
@else
<div class="bg-white border border-ink/10 rounded overflow-x-auto mt-4">
    <table class="w-full text-sm min-w-160">
        <thead>
            <tr class="text-left text-xs text-muted border-b border-ink/10">
                <th class="px-4 py-2">Reference</th>
                <th class="px-4 py-2">Customer</th>
                <th class="px-4 py-2">Zone</th>
                <th class="px-4 py-2">Total</th>
                <th class="px-4 py-2">Status</th>
                <th class="px-4 py-2">Placed</th>
            </tr>
        </thead>
        <tbody>@foreach ($orders as $o)
            <tr class="border-b border-ink/5 last:border-0">
                <td class="px-4 py-2"><a class="text-forest underline underline-offset-4" href="{{ route('admin.orders.show', $o) }}">{{ $o->reference }}</a></td>
                <td class="px-4 py-2">{{ $o->customer_name }}<br><span class="text-xs text-muted">{{ $o->customer_phone }}</span></td>
                <td class="px-4 py-2">{{ ($o->delivery_zone ?? null) === 'outside_dhaka' ? 'Outside Dhaka' : (((string) $o->district) !== '' ? (mb_strtolower(trim((string) $o->district)) === 'dhaka' ? 'Inside Dhaka' : $o->district) : 'Inside Dhaka') }}</td>
                <td class="px-4 py-2">৳{{ number_format((float) $o->total, 0) }}</td>
                <td class="px-4 py-2 capitalize">{{ str_replace('_',' ',$o->status) }}</td>
                <td class="px-4 py-2 text-muted">{{ $o->placed_at?->format('d M, H:i') }}</td>
            </tr>@endforeach
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $orders->links() }}</div>
@endif
@endsection