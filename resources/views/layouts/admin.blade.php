<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title', 'Admin')</title>
    @if (file_exists(public_path('build/manifest.json')))
        @vite(['resources/css/app.css'])
    @else
        <script src="https://cdn.tailwindcss.com"></script>
        <script>
            tailwind.config = { theme: { extend: {
                colors: { ivory: '#FFFFFF', forest: '#14213D', 'forest-deep': '#0a1628', clay: '#2563EB', ink: '#101828', muted: '#667085', linen: '#EEF2F7', moss: '#E3EAF6' },
                fontFamily: { sans: ['Inter','system-ui','sans-serif'], display: ['Fraunces','Georgia','serif'] }
            } } };
        </script>
    @endif

</head>

<body class="bg-ivory text-ink min-h-screen">
    <div class="flex min-h-screen">
        <aside class="hidden md:flex w-60 shrink-0 flex-col border-r border-black/10 bg-white/60">
            <a href="{{ route('admin.dashboard') }}" class="px-5 h-16 flex items-center gap-2 border-b border-black/10">
                <span class="w-8 h-8 rounded-full bg-forest text-white grid place-items-center">D</span>
                <span class="font-display text-lg">{{ config('shop.name') }} <span class="text-xs text-muted">Admin</span></span></a>
            @php $u = auth()->user(); @endphp
            <nav class="p-3 space-y-0.5 text-[15px]" aria-label="Admin">
                <a href="{{ route('admin.dashboard') }}" class="block px-3 py-2.5 rounded-lg border border-transparent hover:border-forest/20 hover:bg-linen/50 {{ request()->routeIs('admin.dashboard') ? 'bg-forest text-white border-forest' : 'text-ink hover:text-forest' }}">Dashboard</a>
                @if ($u?->canManageOrders())<a href="{{ route('admin.orders.index') }}" class="block px-3 py-2.5 rounded-lg border border-transparent hover:border-forest/20 hover:bg-linen/50 {{ request()->routeIs('admin.orders.*') ? 'bg-forest text-white border-forest' : 'text-ink hover:text-forest' }}">Orders</a>@endif
                @if ($u?->canManageProducts())<a href="{{ route('admin.products.index') }}" class="block px-3 py-2.5 rounded-lg border border-transparent hover:border-forest/20 hover:bg-linen/50 {{ request()->routeIs('admin.products.*') ? 'bg-forest text-white border-forest' : 'text-ink hover:text-forest' }}">Products</a>
                <a href="{{ route('admin.categories.index') }}" class="block px-3 py-2.5 rounded-lg border border-transparent hover:border-forest/20 hover:bg-linen/50 {{ request()->routeIs('admin.categories.*') ? 'bg-forest text-white border-forest' : 'text-ink hover:text-forest' }}">Categories</a>@endif
                @if ($u?->canManageInventory())<a href="{{ route('admin.inventory.index') }}" class="block px-3 py-2.5 rounded-lg border border-transparent hover:border-forest/20 hover:bg-linen/50 {{ request()->routeIs('admin.inventory.*') ? 'bg-forest text-white border-forest' : 'text-ink hover:text-forest' }}">Inventory</a>@endif
                @if ($u?->canManageMarketing())<a href="{{ route('admin.marketing.index') }}" class="block px-3 py-2.5 rounded-lg border border-transparent hover:border-forest/20 hover:bg-linen/50 {{ request()->routeIs('admin.marketing.*') ? 'bg-forest text-white border-forest' : 'text-ink hover:text-forest' }}">Marketing</a>@endif
                @if ($u?->canManageSettings())<a href="{{ route('admin.settings.index') }}" class="block px-3 py-2.5 rounded-lg border border-transparent hover:border-forest/20 hover:bg-linen/50 {{ request()->routeIs('admin.settings.*') ? 'bg-forest text-white border-forest' : 'text-ink hover:text-forest' }}">Settings</a>@endif
                @if ($u?->canManageUsers())<a href="{{ route('admin.users.index') }}" class="block px-3 py-2.5 rounded-lg border border-transparent hover:border-forest/20 hover:bg-linen/50 {{ request()->routeIs('admin.users.*') ? 'bg-forest text-white border-forest' : 'text-ink hover:text-forest' }}">Staff & roles</a>@endif
            </nav>
            <div class="mt-auto p-4 border-t border-black/10 text-sm">
                <p class="font-medium">{{ $u?->name }}</p>
                <p class="text-xs text-muted capitalize">{{ str_replace('_', ' ', $u?->role ?? '') }}</p>
                <form action="{{ route('admin.logout') }}" method="POST" class="mt-2">@csrf<button class="text-sm underline underline-offset-4">Log out</button></form>
                <a href="{{ route('home') }}" class="block text-xs text-muted mt-2">View storefront</a>
            </div>
        </aside>
        <div class="flex-1 min-w-0">
            <header class="md:hidden sticky top-0 bg-ivory/95 border-b border-black/10 px-4 h-14 flex items-center justify-between">
                <span>{{ config('shop.name') }} Admin</span>
                <details class="relative">
                    <summary class="text-sm cursor-pointer list-none">Menu</summary>
                    <nav class="absolute right-0 mt-2 w-48 bg-white border border-black/10 rounded-lg p-1.5 space-y-0.5 text-sm shadow-lg">
                        <a class="block px-3 py-2 rounded-lg border border-transparent hover:border-forest/20 hover:bg-linen/50 {{ request()->routeIs('admin.dashboard') ? 'bg-forest text-white border-forest' : 'text-ink hover:text-forest' }}" href="{{ route('admin.dashboard') }}">Dashboard</a>
                        @if ($u?->canManageOrders())
                        <a class="block px-3 py-2 rounded-lg border border-transparent hover:border-forest/20 hover:bg-linen/50 {{ request()->routeIs('admin.orders.*') ? 'bg-forest text-white border-forest' : 'text-ink hover:text-forest' }}" href="{{ route('admin.orders.index') }}">Orders</a>
                        @endif
                        @if ($u?->canManageProducts())
                        <a class="block px-3 py-2 rounded-lg border border-transparent hover:border-forest/20 hover:bg-linen/50 {{ request()->routeIs('admin.products.*') ? 'bg-forest text-white border-forest' : 'text-ink hover:text-forest' }}" href="{{ route('admin.products.index') }}">Products</a>
                        <a class="block px-3 py-2 rounded-lg border border-transparent hover:border-forest/20 hover:bg-linen/50 {{ request()->routeIs('admin.categories.*') ? 'bg-forest text-white border-forest' : 'text-ink hover:text-forest' }}" href="{{ route('admin.categories.index') }}">Categories</a>
                        <a class="block px-3 py-2 rounded-lg border border-transparent hover:border-forest/20 hover:bg-linen/50 {{ request()->routeIs('admin.inventory.*') ? 'bg-forest text-white border-forest' : 'text-ink hover:text-forest' }}" href="{{ route('admin.inventory.index') }}">Inventory</a>
                        @endif
                        @if ($u?->canManageMarketing())<a class="block px-3 py-2 rounded-lg border border-transparent hover:border-forest/20 hover:bg-linen/50 {{ request()->routeIs('admin.marketing.*') ? 'bg-forest text-white border-forest' : 'text-ink hover:text-forest' }}" href="{{ route('admin.marketing.index') }}">Marketing</a>@endif
                        @if ($u?->canManageSettings())<a class="block px-3 py-2 rounded-lg border border-transparent hover:border-forest/20 hover:bg-linen/50 {{ request()->routeIs('admin.settings.*') ? 'bg-forest text-white border-forest' : 'text-ink hover:text-forest' }}" href="{{ route('admin.settings.index') }}">Settings</a>@endif
                        @if ($u?->canManageUsers())<a class="block px-3 py-2 rounded-lg border border-transparent hover:border-forest/20 hover:bg-linen/50 {{ request()->routeIs('admin.users.*') ? 'bg-forest text-white border-forest' : 'text-ink hover:text-forest' }}" href="{{ route('admin.users.index') }}">Staff & roles</a>@endif
                        <form action="{{ route('admin.logout') }}" method="POST" class="px-3 py-2">@csrf<button class="text-sm text-red-600 hover:bg-red-50 rounded-lg border border-red-100 w-full">Log out</button></form>
                    </nav>
                </details>
            </header>
            <main id="admin-main" class="p-4 sm:p-8 max-w-6xl">
                @if (session('success'))<p class="bg-moss text-forest text-sm px-4 py-3 rounded mb-4" role="status">{{ session('success') }}</p>@endif
                @if (session('error'))<p class="bg-red-50 text-red-800 text-sm px-4 py-3 rounded border border-red-200 mb-4" role="alert">{{ session('error') }}</p>@endif
                @if ($errors->any())<div class="bg-red-50 text-red-800 text-sm px-4 py-3 rounded border border-red-200 mb-4" role="alert">
                    <ul class="list-disc pl-5">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                </div>@endif
                @yield('content')
            </main>
        </div>
    </div>
</body>

</html>