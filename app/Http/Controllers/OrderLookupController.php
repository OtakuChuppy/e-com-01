<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderLookupController extends Controller
{
    public function index(): View
    {
        return view('account.orders');
    }

    public function show(Request $request): View
    {
        $validated = $request->validate([
            'reference' => ['required', 'string', 'max:32'],
            'phone' => ['required', 'string', 'max:20'],
        ]);

        $order = Order::with('items')
            ->where('reference', $validated['reference'])
            ->where('customer_phone', $validated['phone'])
            ->firstOrFail();

        session(['order_'.$order->reference => true]);

        return view('account.order-detail', compact('order'));
    }
}
