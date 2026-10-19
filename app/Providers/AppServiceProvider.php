<?php

namespace App\Providers;

use App\Http\Controllers\CartController;
use App\Models\Category;
use App\Models\Setting;
use Illuminate\Support\Facades\URL;
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
        // Storefront content is Bangla; the staff admin panel stays in English.
        app()->setLocale(str_starts_with(request()->path(), 'admin') ? 'en' : 'bn');

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

        // Site identity + SEO settings shared with the storefront layout so the
        // shop name, tagline, contacts and social links are editable from admin.
        View::composer('layouts.store', function ($view) {
            $view->with('siteSettings', Setting::all()->keyBy('key')->map(fn ($s) => $s->value));
        });

        // Force URL scheme to match APP_URL so assets load correctly behind
        // Cloudflare Tunnel (the backend connection is HTTP, but the public
        // URL is HTTPS — without this, asset() generates http:// links that
        // browsers block as mixed content).
        $scheme = parse_url(config('app.url'), PHP_URL_SCHEME) ?? 'http';
        URL::forceScheme($scheme);
    }
}
