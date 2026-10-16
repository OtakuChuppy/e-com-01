<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('shop.name', config('app.name')))</title>
    <meta name="description" content="@yield('meta_description', config('shop.tagline'))">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
        <script>
            tailwind.config = { theme: { extend: {
                colors: { ivory: '#FBFAF7', forest: '#344B3F', forestdeep: '#283B31', clay: '#A96E52', ink: '#242821', muted: '#77796F', linen: '#F2EFE8', moss: '#E7ECE5' },
                fontFamily: { sans: ['Inter','system-ui','sans-serif'], display: ['Fraunces','Georgia','serif'] }
            } } };
        </script>
    @endif
    @stack('head')
</head>
<body class="bg-ivory text-ink font-sans min-h-screen flex flex-col">
    <a href="#main" class="sr-only focus:not-sr-only focus:absolute focus:top-2 focus:left-2 focus:bg-forest focus:text-white focus:px-3 focus:py-2 focus:rounded">Skip to content</a>

    @include('partials.announcement')
    @include('partials.header')

    <main id="main" class="flex-1">
        @if (session('success'))
            <div class="max-w-7xl mx-auto px-4 sm:px-6 pt-4" role="status">
                <p class="bg-moss text-forest text-sm px-4 py-3 rounded">{{ session('success') }}</p>
            </div>
        @endif
        @if (session('error'))
            <div class="max-w-7xl mx-auto px-4 sm:px-6 pt-4" role="alert">
                <p class="bg-red-50 text-red-800 text-sm px-4 py-3 rounded border border-red-200">{{ session('error') }}</p>
            </div>
        @endif
        @yield('content')
    </main>

    @include('partials.footer')
    @include('partials.cart-drawer')

    @stack('scripts')
</body>
</html>
