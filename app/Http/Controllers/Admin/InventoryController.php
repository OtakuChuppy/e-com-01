<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\StockMovement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class InventoryController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate(['q' => ['nullable', 'string', 'max:100']]);
        $query = Product::with('variants')->orderBy('stock_quantity');
        if (! empty($validated['q'])) {
            $q = $validated['q'];
            $query->where(fn ($w) => $w->where('title', 'like', "%{$q}%")->orWhere('sku', 'like', "%{$q}%"));
        }
        $products = $query->paginate(15)->withQueryString();
        $movements = StockMovement::orderByDesc('created_at')->take(15)->get();

        return view('admin.inventory.index', compact('products', 'movements'));
    }

    public function adjust(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'variant_id' => ['nullable', 'exists:product_variants,id'],
            'change_qty' => ['required', 'integer', 'min:-100000', 'max:100000', 'not_in:0'],
            'reason' => ['required', 'in:purchase,adjustment,return,cancelled'],
            'reference' => ['nullable', 'string', 'max:120'],
        ]);

        DB::transaction(function () use ($validated, $request) {
            if ($validated['variant_id']) {
                $variant = ProductVariant::where('product_id', $validated['product_id'])->lockForUpdate()->findOrFail($validated['variant_id']);
                $new = $variant->stock_quantity + $validated['change_qty'];
                if ($new < 0) {
                    throw new \RuntimeException('Adjustment would make variant stock negative.');
                }
                $variant->update(['stock_quantity' => $new]);
                $balance = $new;
            } else {
                $product = Product::lockForUpdate()->findOrFail($validated['product_id']);
                $new = $product->stock_quantity + $validated['change_qty'];
                if ($new < 0) {
                    throw new \RuntimeException('Adjustment would make stock negative.');
                }
                $product->update(['stock_quantity' => $new]);
                $balance = $new;
            }

            StockMovement::create([
                'product_id' => $validated['product_id'],
                'product_variant_id' => $validated['variant_id'] ?? null,
                'change_qty' => $validated['change_qty'],
                'balance_after' => $balance,
                'reason' => $validated['reason'],
                'reference' => $validated['reference'] ?? null,
                'created_by' => $request->user()->id,
            ]);

            AuditLog::create(['user_id' => $request->user()->id, 'action' => 'inventory.adjust',
                'auditable_type' => Product::class, 'auditable_id' => $validated['product_id'],
                'metadata' => ['change' => $validated['change_qty'], 'reason' => $validated['reason']],
                'ip_address' => $request->ip()]);
        });

        return back()->with('success', 'Stock adjusted safely inside a transaction.');
    }
}
