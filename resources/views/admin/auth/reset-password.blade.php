<!DOCTYPE html>
<html lang="en">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="robots" content="noindex, nofollow"><title>Set new password — {{ config('shop.name') }}</title><script src="https://cdn.tailwindcss.com"></script></head>
<body class="bg-ivory text-ink min-h-screen grid place-items-center p-4">
    <main class="w-full max-w-sm">
        <h1 class="text-center font-display text-2xl">Set new password</h1>
        @if ($errors->any())<div class="bg-red-50 text-red-800 text-sm px-4 py-3 rounded border border-red-200 mt-4" role="alert"><ul class="list-disc pl-5">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif
        <form action="{{ route('admin.password.update') }}" method="POST" class="mt-5 space-y-4 bg-white border border-black/10 rounded p-6">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <div><label for="email" class="text-sm font-medium">Email</label><input id="email" name="email" type="email" required value="{{ old('email', $email) }}" class="mt-1 w-full border border-black/15 rounded px-3 py-2.5 text-sm"></div>
            <div><label for="password" class="text-sm font-medium">New password (min 10 chars)</label><input id="password" name="password" type="password" required minlength="10" autocomplete="new-password" class="mt-1 w-full border border-black/15 rounded px-3 py-2.5 text-sm"></div>
            <div><label for="password_confirmation" class="text-sm font-medium">Confirm password</label><input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password" class="mt-1 w-full border border-black/15 rounded px-3 py-2.5 text-sm"></div>
            <button class="w-full bg-forest text-white font-medium px-4 py-2.5 rounded">Update password</button>
        </form>
    </main>
</body>
</html>
