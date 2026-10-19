<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @php $site = $siteSettings ?? []; @endphp
    <title>@yield('title', $site['site_name'] ?? config('shop.name', config('app.name')))</title>
    <meta name="description" content="@yield('meta_description', $site['site_tagline'] ?? config('shop.tagline'))">
    <link rel="icon" href="{{ $site['favicon_url'] ?? asset('favicon.ico') }}">
    <meta property="og:title" content="@yield('og_title', $site['og_title'] ?? $site['site_name'] ?? config('shop.name', config('app.name')))">
    <meta property="og:description" content="@yield('og_description', $site['og_description'] ?? $site['site_tagline'] ?? config('shop.tagline'))">
    <meta property="og:image" content="@yield('og_image', $site['og_image'] ?? $site['logo_url'] ?? '')">
    <meta property="og:url" content="{{ $site['site_url'] ?? config('app.url') }}">
    <meta name="twitter:card" content="@yield('twitter_card', $site['twitter_card'] ?? 'summary')">
    <meta name="twitter:title" content="@yield('twitter_title', $site['og_title'] ?? $site['site_name'] ?? config('shop.name', config('app.name')))">
    <meta name="twitter:description" content="@yield('twitter_description', $site['og_description'] ?? $site['site_tagline'] ?? config('shop.tagline'))">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600&family=Hind+Siliguri:wght@400;500;600;700&family=Inter:wght@400;500;600&family=Noto+Serif+Bengali:wght@500;600&display=swap" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @if (file_exists(public_path('build/manifest.json')))
        @vite(['resources/css/app.css'])
        <link rel="stylesheet" href="{{ asset('css/shop-buttons.css') }}">
    @else
        <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
        <script>
            tailwind.config = { theme: { extend: {
                colors: { ivory: '#FFFFFF', forest: '#14213D', forestdeep: '#0a1628', clay: '#2563EB', ink: '#101828', muted: '#667085', linen: '#EEF2F7', moss: '#E3EAF6' },
                fontFamily: { sans: ['Inter','Hind Siliguri','system-ui','sans-serif'], display: ['Fraunces','Noto Serif Bengali','Georgia','serif'] }
            } } };
        </script>
        <link rel="stylesheet" href="{{ asset('css/shop-buttons.css') }}">
    @endif
    @stack('head')
</head>
<body class="bg-ivory text-ink font-sans min-h-screen flex flex-col">
    <a href="#main" class="skip-link sr-only focus:not-sr-only focus:absolute focus:top-2 focus:left-2 focus:bg-forest focus:text-white focus:px-3 focus:py-2 focus:rounded">কন্টেন্টে যান</a>

    @include('partials.announcement')
    @include('partials.header')

    <main id="main" class="flex-1">
        @if (session('success'))
            <div class="max-w-7xl mx-auto px-4 sm:px-6 pt-4" role="status">
                <div class="toast toast-success animate-scale-in" role="status" aria-live="polite">
                    <span class="toast-icon">&#10004;</span>
                    <span class="toast-body">{{ session('success') }}</span>
                    <button class="toast-close" onclick="this.closest('.toast').remove()">&#215;</button>
                </div>
            </div>
        @endif
        @if (session('error'))
            <div class="max-w-7xl mx-auto px-4 sm:px-6 pt-4" role="alert">
                <div class="toast toast-error animate-scale-in" role="alert" aria-live="assertive">
                    <span class="toast-icon">&#215;</span>
                    <span class="toast-body">{{ session('error') }}</span>
                    <button class="toast-close" onclick="this.closest('.toast').remove()">&#215;</button>
                </div>
            </div>
        @endif
        <div id="toast-container" class="toast-container" aria-live="polite"></div>
        @yield('content')
    </main>

    @unless (request()->routeIs('checkout.index', 'checkout.success', 'products.index', 'products.show'))
        @include('partials.footer')
    @endunless
    @include('partials.cart-drawer')

    @stack('scripts')
    <script>
        window.showToast = function(message, type = 'info') {
            const container = document.getElementById('toast-container');
            if (!container) return;
            const icons = { success: '✓', error: '×', info: 'ℹ' };
            // Built with DOM APIs + textContent on purpose: message must never
            // be interpreted as HTML, no matter who calls showToast in future.
            const el = document.createElement('div');
            el.className = 'toast toast-' + type + ' animate-scale-in';
            el.setAttribute('role', 'status');
            const icon = document.createElement('span');
            icon.className = 'toast-icon';
            icon.textContent = icons[type] || icons.info;
            const body = document.createElement('span');
            body.className = 'toast-body';
            body.textContent = message;
            const close = document.createElement('button');
            close.className = 'toast-close';
            close.textContent = '×';
            close.setAttribute('aria-label', 'Dismiss');
            close.addEventListener('click', function () { el.remove(); });
            el.append(icon, body, close);
            container.appendChild(el);
            setTimeout(() => el.remove(), 5000);
        };
    </script>
</body>
</html>
