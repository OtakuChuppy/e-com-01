@extends('layouts.admin')
@section('title', 'Inventory — Admin')
@section('content')
<h1 class="font-display text-3xl tracking-tight">Inventory</h1>
<p class="text-sm text-muted mt-1">Every change is a transaction with reason, quantity, time and actor. Stock can never go negative.</p>
<div class="grid md:grid-cols-2 gap-4 mt-5">
<div class="bg-white border border-ink/10 rounded p-5">
<h2 class="font-medium">Safe stock adjustment</h2>
<form action="{{ route('admin.inventory.adjust') }}" method="POST" class="mt-3 space-y-3" onsubmit="return confirm('Apply this stock change?');">
@csrf
<label class="text-sm font-medium" for="product_id">Product *</label>
<select id="product_id" name="product_id" required class="w-full border border-ink/15 rounded px-3 py-2 text-sm bg-white">
@foreach (\App\Models\Product::orderBy('title')->get(['id','title','sku','stock_quantity']) as $p)
<option value="{{ $p->id }}">{{ $p->title }} ({{ $p->sku }}) — {{ $p->stock_quantity }}</option>
@endforeach
</select>
<div class="grid grid-cols-2 gap-3">
<div><label class="text-sm font-medium" for="change_qty">Change (+/-) *</label><input id="change_qty" name="change_qty" type="number" required class="mt-1 w-full border border-ink/15 rounded px-3 py-2 text-sm" placeholder="+20 or -5"></div>
<div><label class="text-sm font-medium" for="reason">Reason *</label><select id="reason" name="reason" required class="mt-1 w-full border border-ink/15 rounded px-3 py-2 text-sm bg-white"><option value="purchase">Purchase</option><option value="adjustment">Adjustment</option><option value="return">Return</option><option value="cancelled">Cancelled order</option></select></div>
</div>
<label class="text-sm font-medium" for="reference">Reference (optional)</label>
<input id="reference" name="reference" maxlength="120" class="w-full border border-ink/15 rounded px-3 py-2 text-sm" placeholder="PO-123, order DM-XXXXXX…">
<button class="w-full bg-forest text-white text-sm font-medium px-4 py-2.5 rounded">Apply adjustment</button>
</form>
</div>
<div class="bg-white border border-ink/10 rounded p-5">
<h2 class="font-medium">Recent movements</h2>
@if($movements->isEmpty())<p class="text-sm text-muted mt-2">No movements yet.</p>@endif
<ul class="text-sm mt-2 space-y-1.5">
@foreach($movements as $m)
<li>{{ $m->created_at?->format('d M H:i') }} · product #{{ $m->product_id }} · <span class="font-medium">{{ $m->change_qty > 0 ? '+' : '' }}{{ $m->change_qty }}</span> → {{ $m->balance_after }} · {{ $m->reason }}</li>
@endforeach
</ul>
</div>
</div>
<h2 class="font-medium mt-6 mb-2">Lowest stock first</h2>
<div class="bg-white border border-ink/10 rounded overflow-x-auto">
<table class="w-full text-sm min-w-130">
<thead><tr class="text-left text-xs text-muted border-b border-ink/10"><th class="px-4 py-2">Product</th><th class="px-4 py-2">SKU</th><th class="px-4 py-2">Stock</th><th class="px-4 py-2">Alert at</th></tr></thead>
<tbody>@foreach($products as $p)
<tr class="border-b border-ink/5 last:border-0"><td class="px-4 py-2">{{ $p->title }}</td><td class="px-4 py-2 text-muted">{{ $p->sku }}</td><td class="px-4 py-2 font-medium">{{ $p->stock_quantity }}</td><td class="px-4 py-2">{{ $p->low_stock_threshold }}</td></tr>
@endforeach</tbody></table></div>
<div class="mt-4">{{ $products->links() }}</div>
@endsection
