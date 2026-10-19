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
    // Admin order management: list, view, status change, bulk status, bulk delete.
    // Order status transition nirapod hoye thake (canTransitionBy). Stock shipped
    // order theke restore kora hoye delete korar somoy. Protiti action audit log
    // kora hoyeche.

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
            $this->applyStatusChange($order, $to, $validated['note'] ?? null, $request);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', $this->statusChangeMessage($order, $from, $to));
    }

    public function bulkStatus(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'order_ids' => ['required', 'array', 'min:1'],
            'order_ids.*' => ['integer', 'exists:orders,id'],
            'status' => ['required', 'in:pending,confirmed,on_hold,shipped,delivered,cancelled'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        $orders = Order::whereIn('id', $validated['order_ids'])->get();
        $to = $validated['status'];
        $changed = 0;
        $skipped = 0;
        $failed = [];

        foreach ($orders as $order) {
            if ($order->status === $to || ! $order->canTransitionTo($to)) {
                $skipped++;

                continue;
            }

            try {
                $this->applyStatusChange($order, $to, $validated['note'] ?? null, $request);
                $changed++;
            } catch (\RuntimeException $e) {
                $failed[] = $order->reference;
            }
        }

        $msg = "Status update: {$changed} order(s) moved to {$to}.";
        if ($skipped > 0) {
            $msg .= " {$skipped} skipped (already there or invalid transition).";
        }
        if ($failed !== []) {
            $msg .= ' Could not update: '.implode(', ', $failed).' (insufficient stock).';
        }

        return back()->with($changed === 0 && $failed !== [] ? 'error' : 'success', $msg);
    }

    /**
     * Apply one validated status change: inventory side effects, status history and audit log.
     *
     * @throws \RuntimeException when shipping would exceed available stock
     */
    private function applyStatusChange(Order $order, string $to, ?string $note, Request $request): void
    {
        $from = $order->status;

        DB::transaction(function () use ($order, $to, $from, $request) {
            $order->loadMissing(['items.product.images', 'items.variant']);
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
                $this->restoreStock($order, $request);
            }
        }, 3);

        OrderStatusHistory::create([
            'order_id' => $order->id,
            'from_status' => $from,
            'to_status' => $to,
            'changed_by' => $request->user()->id,
            'note' => $note,
        ]);

        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => 'order.status',
            'auditable_type' => Order::class,
            'auditable_id' => $order->id,
            'metadata' => ['from' => $from, 'to' => $to, 'reference' => $order->reference, 'stock_deducted' => (bool) $order->fresh()->stock_deducted_at],
            'ip_address' => $request->ip(),
        ]);

    }

    public function destroy(Request $request, Order $order): RedirectResponse
    {
        $wasStockDeducted = (bool) $order->stock_deducted_at;

        try {
            DB::transaction(function () use ($order, $request) {
                $this->deleteOrder($order, $request);
            }, 3);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        $this->logOrderDeletion($order, $request, $wasStockDeducted);

        $msg = "Order {$order->reference} deleted.";
        if ($wasStockDeducted) {
            $msg .= ' Inventory restored.';
        }

        return redirect()->route('admin.orders.index')->with('success', $msg);
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'order_ids' => ['required', 'array', 'min:1'],
            'order_ids.*' => ['integer', 'exists:orders,id'],
        ]);

        $orders = Order::with('items')->whereIn('id', $validated['order_ids'])->get();
        $restored = $orders->filter(fn (Order $order): bool => (bool) $order->stock_deducted_at)->count();

        try {
            DB::transaction(function () use ($orders, $request) {
                foreach ($orders as $order) {
                    $this->deleteOrder($order, $request);
                }
            }, 3);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        foreach ($orders as $order) {
            $this->logOrderDeletion($order, $request, (bool) $order->stock_deducted_at);
        }

        $msg = $orders->count().' order(s) deleted.';
        if ($restored > 0) {
            $msg .= " Inventory restored for {$restored} shipped order(s).";
        }

        return redirect()->route('admin.orders.index')->with('success', $msg);
    }

    /**
     * Delete one order, restoring already-deducted inventory first. Call inside a transaction; audit it afterwards.
     */
    private function deleteOrder(Order $order, Request $request): void
    {
        $order->loadMissing(['items']);

        // Deleting a shipped order must not lose the inventory that was deducted.
        if ($order->stock_deducted_at) {
            $this->restoreStock($order, $request);
        }

        // Items and status history cascade via foreign keys.
        $order->delete();
    }

    private function logOrderDeletion(Order $order, Request $request, bool $stockRestored): void
    {
        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => 'order.delete',
            'auditable_type' => Order::class,
            'auditable_id' => $order->id,
            'metadata' => ['reference' => $order->reference, 'status' => $order->status, 'total' => (float) $order->total, 'stock_restored' => $stockRestored],
            'ip_address' => $request->ip(),
        ]);
    }

    private function statusChangeMessage(Order $order, string $from, string $to): string
    {
        $msg = "Order {$order->reference} moved to {$to}.";

        if ($to === 'shipped') {
            $msg .= ' Inventory decreased.';
        }

        if ($to === 'cancelled' && $from === 'shipped') {
            $msg .= ' Inventory restored.';
        }

        return $msg;
    }

    /**
     * Give back inventory for an order whose stock was already deducted at ship time.
     */
    private function restoreStock(Order $order, Request $request): void
    {
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
}
