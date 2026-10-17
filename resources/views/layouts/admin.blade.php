<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title', 'Admin')</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-ivory text-ink min-h-screen">
    <div class="flex min-h-screen">
        <aside class="hidden md:flex w-60 shrink-0 flex-col border-r border-black/10 bg-white/60">
            <a href="{{ route('admin.dashboard') }}" class="px-5 h-16 flex items-center gap-2 border-b border-black/10">
                <span class="w-8 h-8 rounded-full bg-forest text-white grid place-items-center">D</span>
                <span class="font-display text-lg">{{ config('shop.name') }} <span class="text-xs text-muted">Admin</span></span></a>
            @php $u = auth()->user(); @endphp
            <nav class="p-3 space-y-1 text-[15px]" aria-label="Admin">
                <a href="{{ route('admin.dashboard') }}" class="block px-3 py-2 rounded hover:bg-linen">Dashboard</a>
                <a href="{{ route('admin.orders.index') }}" class="block px-3 py-2 rounded hover:bg-linen">Orders</a>
                @if ($u?->canManageProducts())<a href="{{ route('admin.products.index') }}" class="block px-3 py-2 rounded hover:bg-linen">Products</a>@endif
                @if ($u?->canManageInventory())<a href="{{ route('admin.inventory.index') }}" class="block px-3 py-2 rounded hover:bg-linen">Inventory</a>@endif
                @if ($u?->canManageSettings())<a href="{{ route('admin.settings.index') }}" class="block px-3 py-2 rounded hover:bg-linen">Settings</a>@endif
                @if ($u?->canManageUsers())<a href="{{ route('admin.users.index') }}" class="block px-3 py-2 rounded hover:bg-linen">Staff & roles</a>@endif
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
                    <nav class="absolute right-0 mt-2 w-48 bg-white border border-black/10 rounded p-2 space-y-1 text-sm">
                        <a class="block px-3 py-2 rounded hover:bg-linen" href="{{ route('admin.dashboard') }}">Dashboard</a>
                        <a class="block px-3 py-2 rounded hover:bg-linen" href="{{ route('admin.orders.index') }}">Orders</a>
                        <form action="{{ route('admin.logout') }}" method="POST" class="px-3 py-2">@csrf<button>Log out</button></form>
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