<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\StockMovement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'in:pending,confirmed,on_hold,shipped,delivered,cancelled'],
        ]);

        $query = Order::with('items')->orderByDesc('placed_at');

        if (! empty($validated['status'])) {
            $query->where('status', $validated['status']);
        }
        if (! empty($validated['q'])) {
            $q = $validated['q'];
            $query->where(function ($w) use ($q) {
                $w->where('reference', 'like', "%{$q}%")
                    ->orWhere('customer_name', 'like', "%{$q}%")
                    ->orWhere('customer_phone', 'like', "%{$q}%");
            });
        }

        $orders = $query->paginate(15)->withQueryString();

        return view('admin.orders.index', compact('orders'));
    }

    public function show(Order $order): View
    {
        $order->load(['items', 'history', 'customer']);

        return view('admin.orders.show', compact('order'));
    }

    public function updateStatus(Request $request, Order $order): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:pending,confirmed,on_hold,shipped,delivered,cancelled'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        $to = $validated['status'];

        if ($to === $order->status) {
            return back()->with('error', 'Order is already '.$order->status.'. No change made.');
        }

        if (! $order->canTransitionTo($to)) {
            return back()->with('error', "Cannot move order from {$order->status} to {$to}.");
        }

        $from = $order->status;

        try {
            DB::transaction(function () use ($order, $to, $from, $request) {
                $order->loadMissing(['items']);
                $order->update(['status' => $to]);

                // Ship → decrement inventory once; guarded by stock_deducted_at for idempotency.
                if ($to === 'shipped') {
                    if ($order->stock_deducted_at) {
                        return;
                    }
                    foreach ($order->items as $item) {
                        if ($item->product_variant_id) {
                            $variant = ProductVariant::lockForUpdate()->find($item->product_variant_id);
                            if ($variant) {
                                $new = $variant->stock_quantity - $item->quantity;
                                if ($new < 0) {
                                    throw new \RuntimeException("Insufficient stock to ship {$item->name}. Only {$variant->stock_quantity} left.");
                                }
                                $variant->update(['stock_quantity' => $new]);
                                StockMovement::create(['product_id' => $item->product_id ?? $variant->product_id, 'product_variant_id' => $variant->id, 'change_qty' => -$item->quantity, 'balance_after' => $new, 'reason' => 'sale', 'reference' => $order->reference, 'created_by' => $request->user()->id]);
                            }
                        } elseif ($item->product_id) {
                            $product = Product::lockForUpdate()->find($item->product_id);
                            if ($product) {
                                $new = $product->stock_quantity - $item->quantity;
                                if ($new < 0) {
                                    throw new \RuntimeException("Insufficient stock to ship {$item->name}. Only {$product->stock_quantity} left.");
                                }
                                $product->update(['stock_quantity' => $new]);
                                StockMovement::create(['product_id' => $product->id, 'product_variant_id' => null, 'change_qty' => -$item->quantity, 'balance_after' => $new, 'reason' => 'sale', 'reference' => $order->reference, 'created_by' => $request->user()->id]);
                            }
                        }
                    }
                    $order->update(['stock_deducted_at' => now()]);
                }

                // Cancel after shipped → restore inventory once.
                if ($to === 'cancelled' && $from === 'shipped' && $order->stock_deducted_at) {
                    foreach ($order->items as $item) {
                        if ($item->product_variant_id) {
                            $variant = ProductVariant::lockForUpdate()->find($item->product_variant_id);
                            if ($variant) {
                                $new = $variant->stock_quantity + $item->quantity;
                                $variant->update(['stock_quantity' => $new]);
                                StockMovement::create(['product_id' => $item->product_id ?? $variant->product_id, 'product_variant_id' => $variant->id, 'change_qty' => $item->quantity, 'balance_after' => $new, 'reason' => 'cancelled', 'reference' => $order->reference, 'created_by' => $request->user()->id]);
                            }
                        } elseif ($item->product_id) {
                            $product = Product::lockForUpdate()->find($item->product_id);
                            if ($product) {
                                $new = $product->stock_quantity + $item->quantity;
                                $product->update(['stock_quantity' => $new]);
                                StockMovement::create(['product_id' => $product->id, 'product_variant_id' => null, 'change_qty' => $item->quantity, 'balance_after' => $new, 'reason' => 'cancelled', 'reference' => $order->reference, 'created_by' => $request->user()->id]);
                            }
                        }
                    }
                }
            }, 3);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        OrderStatusHistory::create([
            'order_id' => $order->id,
            'from_status' => $from,
            'to_status' => $to,
            'changed_by' => $request->user()->id,
            'note' => $validated['note'] ?? null,
        ]);

        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => 'order.status',
            'auditable_type' => Order::class,
            'auditable_id' => $order->id,
            'metadata' => ['from' => $from, 'to' => $to, 'reference' => $order->reference, 'stock_deducted' => (bool) $order->fresh()->stock_deducted_at],
            'ip_address' => $request->ip(),
        ]);

        $msg = "Order {$order->reference} moved to {$to}.";
        if ($to === 'shipped') {
            $msg .= ' Inventory decreased.';
        }
        if ($to === 'cancelled' && $from === 'shipped') {
            $msg .= ' Inventory restored.';
        }

        return back()->with('success', $msg);
    }
}
