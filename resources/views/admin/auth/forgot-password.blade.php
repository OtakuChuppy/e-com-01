<!DOCTYPE html>
<html lang="en">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="robots" content="noindex, nofollow"><title>Forgot password — {{ config('shop.name') }}</title><script src="https://cdn.tailwindcss.com"></script></head>
<body class="bg-[#FBFAF7] text-[#242821] min-h-screen grid place-items-center p-4">
    <main class="w-full max-w-sm">
        <h1 class="text-center font-display text-2xl">Reset password</h1>
        @if (session('success'))<p class="bg-[#E7ECE5] text-[#344B3F] text-sm px-4 py-3 rounded mt-4" role="status">{{ session('success') }}</p>@endif
        <form action="{{ route('admin.password.email') }}" method="POST" class="mt-5 space-y-4 bg-white border border-black/10 rounded p-6">
            @csrf
            <div><label for="email" class="text-sm font-medium">Staff email</label><input id="email" name="email" type="email" required class="mt-1 w-full border border-black/15 rounded px-3 py-2.5 text-sm"></div>
            <button class="w-full bg-[#344B3F] text-white font-medium px-4 py-2.5 rounded">Send reset link</button>
            <p class="text-center text-sm"><a class="text-[#344B3F] underline underline-offset-4" href="{{ route('admin.login') }}">Back to login</a></p>
        </form>
        <p class="text-xs text-[#77796F] text-center mt-3">With the default log mailer, links appear in server logs — never emailed to strangers.</p>
    </main>
</body>
</html>
