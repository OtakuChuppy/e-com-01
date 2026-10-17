<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Admin login — {{ config('shop.name') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { theme: { extend: {
            colors: { ivory: '#FBFAF7', forest: '#344B3F', forestdeep: '#283B31', clay: '#A96E52', ink: '#242821', muted: '#77796F', linen: '#F2EFE8', moss: '#E7ECE5' },
            fontFamily: { sans: ['Inter','system-ui','sans-serif'], display: ['Fraunces','Georgia','serif'] }
        } } };
    </script>
</head>
<body class="bg-ivory text-ink min-h-screen grid place-items-center p-4">
    <main class="w-full max-w-sm">
        <p class="text-center font-display text-2xl">{{ config('shop.name') }} <span class="text-sm text-muted font-sans">Admin</span></p>
        @if (session('success'))<p class="bg-moss text-forest text-sm px-4 py-3 rounded mt-4" role="status">{{ session('success') }}</p>@endif
        @if (session('error'))<p class="bg-red-50 text-red-800 text-sm px-4 py-3 rounded border border-red-200 mt-4" role="alert">{{ session('error') }}</p>@endif
        @if ($errors->any())
        <div class="bg-red-50 text-red-800 text-sm px-4 py-3 rounded border border-red-200 mt-4" role="alert">
            <ul class="list-disc pl-5">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
        @endif
        <form action="{{ route('admin.login.store') }}" method="POST" class="mt-5 space-y-4 bg-white border border-black/10 rounded p-6">
            @csrf
            <div><label for="email" class="text-sm font-medium">Email</label><input id="email" name="email" type="email" required autocomplete="username" value="{{ old('email') }}" class="mt-1 w-full border border-black/15 rounded px-3 py-2.5 text-sm"></div>
            <div><label for="password" class="text-sm font-medium">Password</label><input id="password" name="password" type="password" required autocomplete="current-password" class="mt-1 w-full border border-black/15 rounded px-3 py-2.5 text-sm"></div>
            <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="remember" value="1" class="rounded"> Remember me</label>
            <button class="w-full bg-forest text-white font-medium px-4 py-2.5 rounded">Log in</button>
            <p class="text-center text-sm"><a class="text-forest underline underline-offset-4" href="{{ route('admin.password.request') }}">Forgot password?</a></p>
        </form>
        <p class="text-center text-xs text-muted mt-4"><a class="hover:text-forest" href="{{ route('home') }}">← Back to storefront</a></p>
    </main>
</body>
</html>
