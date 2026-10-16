@extends('layouts.admin')
@section('title', 'CSV Import — Admin')

@section('content')
<div class="flex items-center justify-between">
    <div>
        <h1 class="font-display text-3xl tracking-tight">Import Products via CSV</h1>
        <p class="text-sm text-muted mt-1">Upload a CSV file to bulk-create or update products. See the <strong>Download template</strong> button for the column reference.</p>
    </div>
    <a href="{{ route('csv_import.template') }}" class="bg-forest text-white text-sm px-4 py-2 rounded">Download template</a>
</div>

@if (session('import_summary'))
    <div class="mt-4 bg-amber-50 border border-amber-200 rounded p-4 text-sm">
        <p class="font-medium text-amber-800">Import summary</p>
        <p class="text-amber-700 mt-1">{{ session('import_summary') }}</p>
    </div>
@endif

@if (session('error'))
    <div class="mt-4 bg-red-50 border border-red-200 rounded p-4 text-sm text-red-700">{{ session('error') }}</div>
@endif

<form method="POST" action="{{ route('csv_import.upload') }}" enctype="multipart/form-data" class="mt-4 space-y-4">
    @csrf
    <div>
        <label class="block text-sm font-medium text-gray-700">CSV file</label>
        <input type="file" name="file" accept=".csv" class="mt-1 block w-full rounded border border-gray-300 px-3 py-2" required>
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700">Default category (optional)</label>
        <select name="category_slug" class="mt-1 block w-full rounded border border-gray-300 px-3 py-2">
            <option value="">— No default —</option>
            @foreach ($categories as $cat)
                <option value="{{ $cat->slug }}">{{ $cat->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="flex gap-2">
        <button class="bg-forest text-white text-sm px-4 py-2 rounded">Import</button>
        <a href="{{ route('admin.products.index') }}" class="text-sm text-gray-600">Cancel</a>
    </div>

    <table class="mt-4 w-full text-sm bg-white border border-gray-200 rounded overflow-hidden">
        <thead>
            <tr>
                <th class="px-3 py-2 bg-gray-100 text-left font-medium text-gray-600">Column</th>
                <th class="px-3 py-2 bg-gray-100 text-left font-medium text-gray-600">Required</th>
                <th class="px-3 py-2 bg-gray-100 text-left font-medium text-gray-600">Description</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($columns as $col => $hint)
                <tr>
                    <td class="px-3 py-2"><code>{{ $col }}</code></td>
                    <td class="px-3 py-2 text-gray-600">@if (in_array($col, ['sku','title','base_price','stock_quantity'])) <span class="text-red-600 font-medium">Yes</span> @else No @endif</td>
                    <td class="px-3 py-2 text-gray-600">{{ $hint }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <p class="text-xs text-gray-500 mt-2">SKU must be unique per product. Blank optional fields are skipped. Variant blocks are identified by the <code>variant_sku</code> field.</p>
</form>
@endsection
