@extends('layouts.store')
@section('title', 'চেকআউট — '.config('shop.name'))
@section('content')
@php $initialDelivery = (float) $deliveryInside; @endphp
<section class="max-w-6xl mx-auto px-4 sm:px-6 pt-8 md:pt-12 pb-28 lg:pb-16"
         data-delivery-inside="{{ $deliveryInside }}"
         data-delivery-outside="{{ $deliveryOutside }}"
         data-subtotal="{{ $totals['subtotal'] }}"
         x-data="{ zone: @js(old('delivery_zone', 'inside_dhaka')), inside: {{ (int) round($deliveryInside) }}, outside: {{ (int) round($deliveryOutside) }}, subtotal: {{ (int) round($totals['subtotal']) }}, submitting: false, fmt(n) { return Math.round(n).toLocaleString('en-US') }, get delivery() { return this.zone === 'inside_dhaka' ? this.inside : this.outside }, get total() { return this.subtotal + this.delivery } }">
    <nav class="text-xs text-muted mb-4" aria-label="পেজের পাথ">
        <a href="{{ route('cart.index') }}" class="hover:text-forest transition-colors">কার্ট</a>
        <span class="mx-1.5" aria-hidden="true">/</span>
        <span class="text-ink font-medium" aria-current="page">চেকআউট</span>
    </nav>
    <div class="section-header">
        <div>
            <h1 class="section-title">চেকআউট</h1>
            <p class="section-subtitle">ক্যাশ অন ডেলিভারি — পার্চেল পৌঁছালে টাকা দিন।</p>
        </div>
        <ol class="hidden sm:flex items-center gap-2.5 text-xs font-medium" aria-label="চেকআউটের অগ্রগতি">
            <li class="flex items-center gap-1.5 text-muted"><span class="w-6 h-6 rounded-full bg-moss text-forest grid place-items-center text-[11px] font-semibold">✓</span>কার্ট</li>
            <li class="w-8 h-px bg-border" aria-hidden="true"></li>
            <li class="flex items-center gap-1.5 text-ink"><span class="w-6 h-6 rounded-full bg-forest text-white grid place-items-center text-[11px] font-semibold">2</span>তথ্য</li>
            <li class="w-8 h-px bg-border" aria-hidden="true"></li>
            <li class="flex items-center gap-1.5 text-muted"><span class="w-6 h-6 rounded-full bg-linen grid place-items-center text-[11px] font-semibold">3</span>কনফার্ম</li>
        </ol>
    </div>
    <div class="grid lg:grid-cols-[1fr_380px] gap-8 lg:gap-10 mt-6 items-start">
        <div>
            <form action="{{ route('checkout.store') }}" method="POST" class="space-y-6" id="checkout-form" @submit="submitting = true">
                @csrf
                <input type="hidden" name="idempotency_key" value="{{ \Illuminate\Support\Str::uuid() }}">
                <div class="bg-white border border-ink/10 rounded-xl p-6 md:p-8 shadow-sm animate-slide-up">
                    <div class="flex items-center gap-3 mb-5">
                        <span class="w-7 h-7 rounded-full bg-moss text-forest grid place-items-center text-xs font-semibold">01</span>
                        <h2 class="font-semibold">যোগাযোগের তথ্য</h2>
                        <span class="ml-auto text-[11px] font-medium uppercase tracking-wider text-muted hidden sm:block">ধাপ ১/৩</span>
                    </div>
                    <div class="grid sm:grid-cols-2 gap-5">
                        <div class="field">
                            <label for="name">পুরো নাম *</label>
                            <input id="name" name="name" value="{{ old('name') }}" required autocomplete="name" placeholder="যেমন: রহিম উদ্দিন" class="input {{ $errors->has('name') ? 'input-error' : '' }}">
                            @error('name')<p class="field-error">{{ $message }}</p>@enderror
                        </div>
                        <div class="field">
                            <label for="phone">ফোন *</label>
                            <input id="phone" name="phone" value="{{ old('phone') }}" required autocomplete="tel" inputmode="tel" placeholder="01XXXXXXXXX" class="input {{ $errors->has('phone') ? 'input-error' : '' }}">
                            @error('phone')<p class="field-error">{{ $message }}</p>@enderror
                        </div>
                    </div>
                    <div class="field mt-5">
                        <label for="email">ইমেইল <span class="text-muted font-normal">(ঐচ্ছিক — অর্ডার আপডেটের জন্য)</span></label>
                        <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" placeholder="you@example.com" class="input {{ $errors->has('email') ? 'input-error' : '' }}">
                        @error('email')<p class="field-error">{{ $message }}</p>@enderror
                    </div>
                </div>
                <div class="bg-white border border-ink/10 rounded-xl p-6 md:p-8 shadow-sm animate-slide-up" style="animation-delay: .06s">
                    <div class="flex items-center gap-3 mb-5">
                        <span class="w-7 h-7 rounded-full bg-moss text-forest grid place-items-center text-xs font-semibold">02</span>
                        <h2 class="font-semibold">ডেলিভারি ঠিকানা</h2>
                        <span class="ml-auto text-[11px] font-medium uppercase tracking-wider text-muted hidden sm:block">ধাপ ২/৩</span>
                    </div>
                    <div class="field">
                        <label for="address_line1">পুরো ঠিকানা *</label>
                        <input id="address_line1" name="address_line1" value="{{ old('address_line1') }}" required autocomplete="street-address" placeholder="বাড়ি, রোড, এলাকা" class="input {{ $errors->has('address_line1') ? 'input-error' : '' }}">
                        @error('address_line1')<p class="field-error">{{ $message }}</p>@enderror
                        <p class="field-note">কুরিয়ার সহজে খুঁজে পায় এজন্য একটি ল্যান্ডমার্ক লিখুন।</p>
                    </div>
                    <div class="mt-5">
                        <span class="text-sm font-medium block mb-2" id="zone-label">ডেলিভারি জোন *</span>
                        <div class="grid sm:grid-cols-2 gap-3" role="radiogroup" aria-labelledby="zone-label">
                            <label class="zone-card relative flex items-center gap-3 border rounded-xl px-4 py-4 text-sm bg-white cursor-pointer transition-all hover:border-forest/60 has-checked:border-forest has-checked:bg-moss/50 has-checked:ring-1 has-checked:ring-forest/40 focus-within:ring-2 focus-within:ring-forest/40"
                                   :class="zone === 'inside_dhaka' ? 'border-forest bg-moss/50 ring-1 ring-forest/40' : 'border-ink/15'">
                                <input type="radio" name="delivery_zone" value="inside_dhaka" x-model="zone" @checked(old('delivery_zone', 'inside_dhaka') === 'inside_dhaka') class="sr-only">
                                <span class="w-5 h-5 rounded-full border-2 grid place-items-center shrink-0 transition-colors" :class="zone === 'inside_dhaka' ? 'border-forest' : 'border-ink/25'" aria-hidden="true">
                                    <span class="w-2.5 h-2.5 rounded-full bg-forest transition-transform" :class="zone === 'inside_dhaka' ? 'scale-100' : 'scale-0'"></span>
                                </span>
                                <span>
                                    <span class="font-medium block">ঢাকার ভিতরে</span>
                                    <span class="text-xs text-muted block mt-0.5">৳{{ number_format($deliveryInside, 0) }} · ১–২ দিন</span>
                                </span>
                            </label>
                            <label class="zone-card relative flex items-center gap-3 border rounded-xl px-4 py-4 text-sm bg-white cursor-pointer transition-all hover:border-forest/60 has-checked:border-forest has-checked:bg-moss/50 has-checked:ring-1 has-checked:ring-forest/40 focus-within:ring-2 focus-within:ring-forest/40"
                                   :class="zone === 'outside_dhaka' ? 'border-forest bg-moss/50 ring-1 ring-forest/40' : 'border-ink/15'">
                                <input type="radio" name="delivery_zone" value="outside_dhaka" x-model="zone" @checked(old('delivery_zone') === 'outside_dhaka') class="sr-only">
                                <span class="w-5 h-5 rounded-full border-2 grid place-items-center shrink-0 transition-colors" :class="zone === 'outside_dhaka' ? 'border-forest' : 'border-ink/25'" aria-hidden="true">
                                    <span class="w-2.5 h-2.5 rounded-full bg-forest transition-transform" :class="zone === 'outside_dhaka' ? 'scale-100' : 'scale-0'"></span>
                                </span>
                                <span>
                                    <span class="font-medium block">ঢাকার বাইরে</span>
                                    <span class="text-xs text-muted block mt-0.5">৳{{ number_format($deliveryOutside, 0) }} · ২–৪ দিন</span>
                                </span>
                            </label>
                        </div>
                        @error('delivery_zone')<p class="field-error mt-2">{{ $message }}</p>@enderror
                    </div>
                </div>
                <div class="bg-white border border-ink/10 rounded-xl p-6 md:p-8 shadow-sm animate-slide-up" style="animation-delay: .12s">
                    <div class="flex items-center gap-3 mb-5">
                        <span class="w-7 h-7 rounded-full bg-moss text-forest grid place-items-center text-xs font-semibold">03</span>
                        <h2 class="font-semibold">পেমেন্ট</h2>
                        <span class="ml-auto text-[11px] font-medium uppercase tracking-wider text-muted hidden sm:block">ধাপ ৩/৩</span>
                    </div>
                    <div class="flex items-start gap-4 border border-forest/30 bg-moss/40 rounded-xl px-4 py-4">
                        <span class="w-10 h-10 rounded-full bg-forest text-white grid place-items-center shrink-0" aria-hidden="true">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="2.5" y="6" width="19" height="12" rx="2"/><circle cx="12" cy="12" r="2.6"/><path d="M6 9.5h.01M18 14.5h.01"/></svg>
                        </span>
                        <div class="text-sm">
                            <p class="font-semibold">ক্যাশ অন ডেলিভারি</p>
                            <p class="text-muted mt-1">পার্চেল পৌঁছালে নগদে টাকা দিন। কোনো অগ্রিম নয়, কার্ডও লাগবে না।</p>
                        </div>
                        <span class="hidden sm:inline-flex items-center ml-auto text-[11px] font-semibold uppercase tracking-wider text-forest bg-white border border-forest/20 rounded-full px-2.5 py-1 shrink-0">নির্বাচিত</span>
                    </div>
                </div>
                <p class="text-xs text-muted text-center">অর্ডার করার সময় দাম ও স্টক আমাদের সার্ভারে আবার যাচাই করা হয়। ডাবল-ক্লিক করলেও সমস্যা নেই — ডুপ্লিকেট অর্ডার ব্লক হয়ে যায়।</p>
                <p class="text-center"><a href="{{ route('cart.index') }}" class="text-sm text-forest underline underline-offset-4">কার্টে ফিরে যান</a></p>
            </form>
        </div>
        <aside class="lg:sticky lg:top-20 animate-slide-up" style="animation-delay: .1s" aria-label="অর্ডার সারাংশ">
            <div class="bg-white border border-ink/10 rounded-xl shadow-sm overflow-hidden">
                <div class="px-6 py-5 border-b border-ink/10 flex items-center justify-between">
                    <h2 class="font-semibold">অর্ডার সারাংশ</h2>
                    <span class="text-xs font-medium text-muted">{{ $totals['count'] }}টি আইটেম{{ 1 === 1 ? '' : 's' }}</span>
                </div>
                <ul class="px-6 py-4 space-y-4 max-h-80 overflow-y-auto thin-scroll">
                    @foreach ($cart->items as $item)
                    @php
                        $p = $item->product;
                        $price = (float) ($item->variant?->price_override ?? $p->base_price);
                        $im = $p->primaryImage();
                        $s = $im?->path;
                        if ($s && ! str_starts_with($s, 'http')) { $s = asset('storage/'.$s); }
                    @endphp
                    <li class="flex gap-3 items-center">
                        <div class="relative w-14 h-14 bg-lined rounded-lg overflow-hidden shrink-0">
                            <img src="{{ $s ?? 'https://images.unsplash.com/photo-1441986300917-64674bd600d8?w=300&q=60&auto=format&fit=crop' }}" alt="{{ $p->title }}" class="w-full h-full object-cover" loading="lazy">
                            @if ($item->variant)<span class="absolute bottom-0.5 left-0.5 bg-ink/80 text-white text-[9px] px-1 py-px rounded">{{ $item->variant->name }}</span>@endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium line-clamp-1">{{ $item->variant?->name ?? $p->title }}</p>
                            <p class="text-xs text-muted mt-0.5">পরিমাণ {{ $item->quantity }} × ৳{{ number_format($price, 0) }}</p>
                        </div>
                        <p class="text-sm font-semibold">৳{{ number_format($price * $item->quantity, 0) }}</p>
                    </li>
                    @endforeach
                </ul>
                <div class="px-6 py-5 border-t border-ink/10 bg-sand space-y-2.5 text-sm">
                    <div class="flex justify-between">
                        <span class="text-muted">উপমোট</span>
                        <span class="font-medium">৳{{ number_format($totals['subtotal'], 0) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-muted">ডেলিভারি <span x-text="zone === 'inside_dhaka' ? '(ঢাকার ভিতরে)' : '(ঢাকার বাইরে)'"></span></span>
                        <span class="font-medium">৳<span x-text="delivery">{{ (int) round($initialDelivery) }}</span></span>
                    </div>
                    <div class="flex justify-between items-baseline pt-2.5 border-t border-ink/10">
                        <span class="font-semibold">মোট (COD)</span>
                        <span class="font-display text-xl font-semibold">৳<span x-text="fmt(total)">{{ number_format($totals['subtotal'] + $initialDelivery, 0) }}</span></span>
                    </div>
                </div>
                <div class="px-6 py-5 border-t border-ink/10">
                    <div class="hidden lg:block">
                        <button type="submit" form="checkout-form" id="place-btn" data-place-btn class="btn btn-cta btn-lg w-full text-base"
                                x-bind:disabled="submitting"
                                x-text="submitting ? 'অর্ডার করা হচ্ছে…' : 'অর্ডার করুন · ৳' + fmt(total) + ' · ক্যাশ অন ডেলিভারি'">অর্ডার করুন · ৳{{ number_format($totals['subtotal'] + $deliveryInside, 0) }} ক্যাশ অন ডেলিভারি</button>
                    </div>
                    <p class="text-xs text-muted text-center mt-3">শুধু ক্যাশ অন ডেলিভারি। পাঠানোর আগে কনফার্ম করতে আমরা কল করব।</p>
                    <p class="text-center mt-2"><a href="{{ route('cart.index') }}" class="text-sm text-forest underline underline-offset-4">কার্ট সম্পাদনা</a></p>
                </div>
            </div>
            <ul class="mt-4 grid grid-cols-3 gap-2 text-center text-[11px] text-muted">
                <li class="border border-ink/10 rounded-lg py-3 px-2 bg-white">
                    <span class="text-forest mx-auto mb-1 block" aria-hidden="true"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10"/><path d="M9 12l2 2 4-4"/></svg></span>
                    নিরাপদ চেকআউট
                </li>
                <li class="border border-ink/10 rounded-lg py-3 px-2 bg-white">
                    <span class="text-forest mx-auto mb-1 block" aria-hidden="true"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><path d="M8.5 12.5l2.5 2.5 4.5-5"/></svg></span>
                    যাচাইকৃত অর্ডার
                </li>
                <li class="border border-ink/10 rounded-lg py-3 px-2 bg-white">
                    <span class="text-forest mx-auto mb-1 block" aria-hidden="true"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="2.5" y="6" width="19" height="12" rx="2"/><circle cx="12" cy="12" r="2.6"/></svg></span>
                    ক্যাশ অন ডেলিভারি
                </li>
            </ul>
        </aside>
    </div>
    <div class="mobile-sticky bg-ivory border-t border-ink/10 p-4 shadow-lg">
        <div class="flex items-center justify-between gap-4">
            <div>
                <p class="text-xs text-muted leading-tight">মোট (COD)</p>
                <p class="font-semibold text-lg leading-tight">৳<span x-text="fmt(total)">{{ number_format($totals['subtotal'] + $initialDelivery, 0) }}</span></p>
            </div>
            <button type="submit" form="checkout-form" class="btn btn-cta btn-lg flex-1 sm:max-w-64"
                    x-bind:disabled="submitting"
                    x-text="submitting ? 'অর্ডার করা হচ্ছে…' : 'অর্ডার করুন'">অর্ডার করুন</button>
        </div>
    </div>
</section>
@endsection