<?php

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CsvImportController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\InventoryController;
use App\Http\Controllers\Admin\MarketingController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\ProductImageController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\OrderLookupController;
use App\Http\Controllers\PolicyController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SitemapController;
use App\Http\Middleware\EnsureRole;
use Illuminate\Support\Facades\Route;

Route::get('/health', [HealthController::class, 'show'])->name('health');

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/{slug}', [ProductController::class, 'show'])->name('products.show');

Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart', [CartController::class, 'store'])->name('cart.store')->middleware('throttle:30,1');
Route::patch('/cart/{item}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/cart/{item}', [CartController::class, 'destroy'])->name('cart.destroy');

Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store')->middleware('throttle:10,1');
Route::get('/checkout/success/{reference}', [CheckoutController::class, 'success'])->name('checkout.success');

Route::get('/track-order', [OrderLookupController::class, 'index'])->name('account.orders');
Route::get('/track-order/lookup', [OrderLookupController::class, 'show'])->name('account.orders.show')->middleware('throttle:30,1');

Route::get('/policies/{page}', [PolicyController::class, 'show'])->name('policies.show');

Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');
Route::get('/robots.txt', [SitemapController::class, 'robots'])->name('robots');

// ---- Staff auth (rate-limited) ----
Route::middleware('throttle:20,1')->group(function () {
    Route::get('/admin/login', [LoginController::class, 'show'])->name('admin.login');
    Route::post('/admin/login', [LoginController::class, 'store'])->name('admin.login.store');
    Route::get('/admin/forgot-password', [PasswordResetController::class, 'showLinkForm'])->name('admin.password.request');
    Route::post('/admin/forgot-password', [PasswordResetController::class, 'sendLink'])->name('admin.password.email');
    Route::get('/admin/reset-password/{token}', [PasswordResetController::class, 'showResetForm'])->name('admin.password.reset');
    Route::post('/admin/reset-password', [PasswordResetController::class, 'reset'])->name('admin.password.update');
});
Route::post('/admin/logout', [LoginController::class, 'destroy'])->name('admin.logout')->middleware('auth');

// ---- Admin panel ----

Route::prefix('admin')->name('admin.')->middleware(['auth', EnsureRole::class.':any-staff'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Orders: owner/admin/order_manager only (marketers get dashboard + marketing only)
    Route::middleware([EnsureRole::class.':owner,admin,order_manager'])->group(function () {
        Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
        Route::patch('/orders/bulk/status', [OrderController::class, 'bulkStatus'])->name('orders.bulk-status');
        Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
        Route::patch('/orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.status');
    });

    // Bulk delete from the multi-select orders list — owner/admin only (mirrors products.destroy)
    Route::delete('/orders/bulk', [OrderController::class, 'bulkDestroy'])
        ->middleware([EnsureRole::class.':owner,admin'])
        ->name('orders.bulk-destroy');

    // Deleting an order is irreversible — owner/admin only (mirrors products.destroy)
    Route::delete('/orders/{order}', [OrderController::class, 'destroy'])
        ->middleware([EnsureRole::class.':owner,admin'])
        ->name('orders.destroy');

    // Marketing (tracking + SEO): owner + marketer only — never admin/order_manager
    Route::middleware([EnsureRole::class.':owner,marketer'])->group(function () {
        Route::get('/marketing', [MarketingController::class, 'index'])->name('marketing.index');
        Route::patch('/marketing', [MarketingController::class, 'update'])->name('marketing.update');
    });

    // Products + inventory: owner/admin only (server-side, not just hidden buttons)
    Route::middleware([EnsureRole::class.':owner,admin'])->group(function () {
        Route::get('/products', [App\Http\Controllers\Admin\ProductController::class, 'index'])->name('products.index');
        Route::get('/products/create', [App\Http\Controllers\Admin\ProductController::class, 'create'])->name('products.create');
        Route::post('/products', [App\Http\Controllers\Admin\ProductController::class, 'store'])->name('products.store');
        Route::get('/products/{product}', [App\Http\Controllers\Admin\ProductController::class, 'show'])->name('products.show');
        Route::get('/products/{product}/edit', [App\Http\Controllers\Admin\ProductController::class, 'edit'])->name('products.edit');
        Route::patch('/products/{product}', [App\Http\Controllers\Admin\ProductController::class, 'update'])->name('products.update');
        Route::patch('/products/{product}/status', [App\Http\Controllers\Admin\ProductController::class, 'setStatus'])->name('products.status');
        Route::delete('/products/{product}', [App\Http\Controllers\Admin\ProductController::class, 'destroy'])->name('products.destroy');

        // Product image management (upload / delete / set primary)
        Route::post('/products/{product}/images', [ProductImageController::class, 'store'])->name('products.images.store');
        Route::delete('/products/{product}/images/{image}', [ProductImageController::class, 'destroy'])->name('products.images.destroy');
        Route::patch('/products/{product}/images/{image}/primary', [ProductImageController::class, 'setPrimary'])->name('products.images.primary');

        Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
        Route::post('/inventory/adjust', [InventoryController::class, 'adjust'])->name('inventory.adjust');

        // Categories: owner/admin only — create, browse and view products linked to each
        Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
        Route::get('/categories/create', [CategoryController::class, 'create'])->name('categories.create');
        Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
        Route::get('/categories/{category}', [CategoryController::class, 'show'])->name('categories.show');
        Route::get('/categories/{category}/edit', [CategoryController::class, 'edit'])->name('categories.edit');
        Route::patch('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
        Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');

        // CSV import: any staff (template + validation + transactions)
        Route::get('/csv-import/template', [CsvImportController::class, 'downloadTemplate'])->name('csv_import.template');
        Route::post('/csv-import/upload', [CsvImportController::class, 'upload'])->name('csv_import.upload');

        Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
        Route::patch('/settings', [SettingController::class, 'update'])->name('settings.update');
    });

    // Users: owner only
    Route::middleware([EnsureRole::class.':owner'])->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::patch('/users/{user}', [UserController::class, 'update'])->name('users.update');
    });
});

/*
 * ============================================================================
 * TODO (planned features — not done yet)
 * ============================================================================
 * Nicher feature-gulo ekhon implement kora hoyni, porikolpona ache:
 *
 * 1. Real payment gateway (bKash, Nagad, SSLCOMMERZ, Stripe).
 *    Ekhon shudhumatro COD (cash on delivery) supported. Checkout-e payment
 *    method 'cod' hard-coded. Agami sprint-e gateway integration porikolpona ache.
 *
 * 2. Customer registration / login.
 *    Ekhon guest checkout (registration chara). Customer account, order history,
 *    saved address er jonno auth system lagbe.
 *
 * 3. Email order invoices & notifications.
 *    MAIL_MAILER=log ekhon development-e. Real order confirmation, shipping
 *    update ebong invoice email pathanor system lagbe.
 *
 * 4. Product reviews & ratings.
 *    Product page-e customer review ebong star rating system lagbe.
 *
 * 5. Discount coupons / promo codes.
 *    Checkout-e coupon apply korar system nai. Agami feature hishebe porikolpona ache.
 *
 * 6. Dark mode.
 *    Storefront-ti ekhon shudhumatro light mode. Tailwind dark mode strategy
 *    byabohar kore dark mode add kora jay.
 *
 * 7. Inventory reservation at checkout time.
 *    Ekhon order place korar somoy stock shudhumatro check kora hoy, reserve
 *    nai. Staff order 'shipped' korar por stock kome. Race condition er jonno
 *    checkout-e time-bound reservation jukto kora jay.
 *
 * 8. Strict Content-Security-Policy (CSP).
 *    SecurityHeaders middleware-e CSP ektu hold kora hoyeche karon storefront
 *    inline script (Alpine, Tailwind CDN) byabohar kore. Non-inline script-e
 *    rupantor por full CSP enabe kora jay.
 *
 * 9. Password strength / breach-check policy.
 *    User creation ebong password reset-e ekhon min:10 char check. Agami-e
 *    password strength meter ebong breach database check jukto kora jay.
 * ============================================================================
 */
