@extends('layouts.store')
@section('title', 'Track your order — '.config('shop.name'))
@section('content')
<section class="max-w-xl mx-auto px-4 sm:px-6 pt-8">
    <h1 class="font-display text-3xl tracking-tight">Track your order</h1>
    <p class="text-sm text-muted mt-1">Enter the order reference from your confirmation and the phone number used at checkout.</p>
    <form action="{{ route('account.orders.show') }}" method="GET" class="mt-5 space-y-4">
        <div><label for="reference" class="text-sm font-medium">Order reference</label><input id="reference" name="reference" required placeholder="DM-XXXXXX" value="{{ request('reference') }}" class="mt-1 w-full border border-ink/15 rounded px-3 py-2.5 text-sm bg-white"></div>
        <div><label for="phone" class="text-sm font-medium">Phone number</label><input id="phone" name="phone" required placeholder="01XXXXXXXXX" class="mt-1 w-full border border-ink/15 rounded px-3 py-2.5 text-sm bg-white"></div>
        <button class="w-full bg-forest text-white font-medium px-6 py-3 rounded">Look up order</button>
    </form>
</section>
@endsection
