<?php

namespace App\Providers;

use App\Http\Controllers\CartController;
use App\Models\Category;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        View::composer(['partials.header', 'partials.footer', 'partials.cart-drawer'], function ($view) {
            $view->with('navCategories', Category::active()->orderBy('sort_order')->take(4)->get());

            try {
                $cart = app(CartController::class)->cart(request());
                $cart->loadMissing(['items']);
                $totals = app(CartController::class)->totals($cart);
                $view->with('cartCount', $totals['count']);
                $view->with('cartItems', $cart->items ?? collect());
            } catch (\Throwable) {
                $view->with('cartCount', 0);
                $view->with('cartItems', collect());
            }
        });
    }
}
