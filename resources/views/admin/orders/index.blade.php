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
<div class="flex flex-wrap items-center gap-2 mt-4 bg-white border border-ink/10 rounded p-3"
     data-bulk-status-url="{{ route('admin.orders.bulk-status') }}"
     data-bulk-delete-url="{{ route('admin.orders.bulk-destroy') }}">
    <span class="text-sm font-medium">Bulk:</span>
    <label for="bulk-status" class="sr-only">Bulk status</label>
    <select id="bulk-status" class="border border-ink/15 rounded px-3 py-2 text-sm bg-white">
        @foreach (['pending','confirmed','on_hold','shipped','delivered','cancelled'] as $s)
        <option value="{{ $s }}">{{ ucfirst(str_replace('_',' ',$s)) }}</option>
        @endforeach
    </select>
    <button type="button" id="bulk-status-apply" class="bg-forest text-white text-sm px-4 py-2 rounded">Apply status</button>
    @if (auth()->user()?->canDeleteOrders())
    <button type="button" id="bulk-delete" class="border border-red-300 text-red-700 bg-white hover:bg-red-50 text-sm px-4 py-2 rounded">Delete selected</button>
    @endif
    <span class="text-xs text-muted" id="bulk-count">0 selected</span>
</div>
<div class="bg-white border border-ink/10 rounded overflow-x-auto mt-4">
    <table class="w-full text-sm min-w-160">
        <thead>
            <tr class="text-left text-xs text-muted border-b border-ink/10">
                <th class="px-4 py-2"><input type="checkbox" id="order-select-all" aria-label="Select all orders on this page"></th>
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
                <td class="px-4 py-2"><input type="checkbox" class="order-select" value="{{ $o->id }}" aria-label="Select order {{ $o->reference }}"></td>
                <td class="px-4 py-2"><a class="text-forest underline underline-offset-4" href="{{ route('admin.orders.show', $o) }}">{{ $o->reference }}</a></td>
                <td class="px-4 py-2">{{ $o->customer_name }}<br><span class="text-xs text-muted">{{ $o->customer_phone }}</span></td>
                <td class="px-4 py-2">{{ ($o->delivery_zone ?? null) === 'outside_dhaka' ? 'Outside Dhaka' : (((string) $o->district) !== '' ? (mb_strtolower(trim((string) $o->district)) === 'dhaka' ? 'Inside Dhaka' : $o->district) : 'Inside Dhaka') }}</td>
                <td class="px-4 py-2">৳{{ number_format((float) $o->total, 0) }}</td>
                <td class="px-4 py-2">
                    @php $nextStatuses = \App\Models\Order::TRANSITIONS[$o->status] ?? []; @endphp
                    @if ($nextStatuses === [])
                        <span class="capitalize">{{ str_replace('_',' ',$o->status) }}</span>
                    @else
                        <form action="{{ route('admin.orders.status', $o) }}" method="POST">
                            @csrf @method('PATCH')
                            <label for="status-{{ $o->id }}" class="sr-only">Change status for {{ $o->reference }}</label>
                            <select id="status-{{ $o->id }}" name="status" class="border border-ink/15 rounded px-2 py-1.5 text-xs bg-white capitalize cursor-pointer"
                                    onchange="if (confirm('Change this order to ' + this.value + (this.value === 'shipped' ? ' and DECREASE inventory?' : '?'))) { this.form.submit(); } else { this.selectedIndex = 0; }">
                                <option value="" selected disabled>{{ str_replace('_',' ',$o->status) }}</option>
                                @foreach ($nextStatuses as $next)
                                <option value="{{ $next }}">{{ ucfirst(str_replace('_',' ',$next)) }}</option>
                                @endforeach
                            </select>
                        </form>
                    @endif
                </td>
                <td class="px-4 py-2 text-muted">{{ $o->placed_at?->format('d M, H:i') }}</td>
            </tr>@endforeach
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $orders->links() }}</div>
@endif
<script>
(function () {
    var bar = document.querySelector('[data-bulk-status-url]');
    if (!bar) return;
    var allBox = document.getElementById('order-select-all');
    var countLabel = document.getElementById('bulk-count');

    function selectedIds() {
        return Array.prototype.map.call(document.querySelectorAll('.order-select:checked'), function (box) { return box.value; });
    }

    function refreshCount() {
        var rows = document.querySelectorAll('.order-select');
        var ids = selectedIds();
        if (countLabel) countLabel.textContent = ids.length + ' selected';
        if (allBox) allBox.checked = rows.length > 0 && ids.length === rows.length;
    }

    document.querySelectorAll('.order-select').forEach(function (box) {
        box.addEventListener('change', refreshCount);
    });
    if (allBox) {
        allBox.addEventListener('change', function () {
            document.querySelectorAll('.order-select').forEach(function (box) { box.checked = allBox.checked; });
            refreshCount();
        });
    }

    function hiddenInput(name, value) {
        var input = document.createElement('input');
        input.type = 'hidden';
        input.name = name;
        input.value = value;
        return input;
    }

    function submitBulk(url, method, fields, confirmMessage) {
        var ids = selectedIds();
        if (ids.length === 0) {
            alert('Select at least one order first.');
            return;
        }
        if (!confirm(confirmMessage + ' (' + ids.length + ' selected)')) return;
        // Built with DOM APIs on purpose: values must never be parsed as HTML.
        var form = document.createElement('form');
        form.method = 'POST';
        form.action = url;
        var token = document.querySelector('meta[name="csrf-token"]');
        form.appendChild(hiddenInput('_token', token ? token.getAttribute('content') : ''));
        form.appendChild(hiddenInput('_method', method));
        ids.forEach(function (id) {
            form.appendChild(hiddenInput('order_ids[]', id));
        });
        for (var name in fields) {
            form.appendChild(hiddenInput(name, fields[name]));
        }
        document.body.appendChild(form);
        form.submit();
    }

    var statusBtn = document.getElementById('bulk-status-apply');
    if (statusBtn) {
        statusBtn.addEventListener('click', function () {
            var select = document.getElementById('bulk-status');
            var label = select.options[select.selectedIndex].text;
            submitBulk(bar.getAttribute('data-bulk-status-url'), 'PATCH', {status: select.value},
                'Apply "' + label + '" to the selected orders? Only valid transitions will be applied; the rest are skipped.');
        });
    }

    var deleteBtn = document.getElementById('bulk-delete');
    if (deleteBtn) {
        deleteBtn.addEventListener('click', function () {
            submitBulk(bar.getAttribute('data-bulk-delete-url'), 'DELETE', {},
                'Delete the selected orders permanently? Shipped orders get their inventory restored first.');
        });
    }
})();
</script>
@endsection