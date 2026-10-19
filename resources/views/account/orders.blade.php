@extends('layouts.store')
@section('title', 'অর্ডার ট্র্যাক করুন — '.config('shop.name'))
@section('content')
<section class="max-w-xl mx-auto px-4 sm:px-6 pt-8 md:pt-12">
    <div class="section-header">
        <div>
            <h1 class="section-title">অর্ডার ট্র্যাক করুন</h1>
            <p class="section-subtitle">কনফার্মেশনে পাওয়া অর্ডার রেফারেন্স এবং চেকআউটে ব্যবহৃত ফোন নম্বর দিন।</p>
        </div>
    </div>
    <form action="{{ route('account.orders.show') }}" method="GET" class="mt-6 space-y-4 card card-sm p-6 md:p-8">
        <div class="field"><label for="reference" class="text-sm font-medium">অর্ডার রেফারেন্স</label><input id="reference" name="reference" required placeholder="DM-XXXXXX" value="{{ request('reference') }}" class="input">@error('reference')<p class="field-error">{{ $message }}</p>@enderror</div>
        <div class="field"><label for="phone" class="text-sm font-medium">ফোন নম্বর</label><input id="phone" name="phone" required placeholder="01XXXXXXXXX" class="input">@error('phone')<p class="field-error">{{ $message }}</p>@enderror</div>
        <button class="w-full btn btn-primary btn-md">অর্ডার খুঁজুন</button>
    </form>
</section>
@endsection
