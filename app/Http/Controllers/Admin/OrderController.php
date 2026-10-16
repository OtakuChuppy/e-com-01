<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
        $order->update(['status' => $to]);

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
            'metadata' => ['from' => $from, 'to' => $to, 'reference' => $order->reference],
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', "Order {$order->reference} moved to {$to}.");
    }
}
