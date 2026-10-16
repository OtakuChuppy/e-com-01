<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $counts = [
            'total' => Order::count(),
            'new' => Order::where('status', 'pending')->count(),
            'on_hold' => Order::where('status', 'on_hold')->count(),
            'confirmed' => Order::where('status', 'confirmed')->count(),
            'delivered' => Order::where('status', 'delivered')->count(),
            'cancelled' => Order::where('status', 'cancelled')->count(),
        ];

        $recent = Order::with('items')->orderByDesc('placed_at')->take(8)->get();

        $sales7 = Order::whereIn('status', ['confirmed', 'shipped', 'delivered'])
            ->where('placed_at', '>=', now()->subDays(7))
            ->sum('total');

        $sales30 = Order::whereIn('status', ['confirmed', 'shipped', 'delivered'])
            ->where('placed_at', '>=', now()->subDays(30))
            ->sum('total');

        return view('admin.dashboard', compact('counts', 'recent', 'sales7', 'sales30'));
    }
}
