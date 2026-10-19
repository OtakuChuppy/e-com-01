@extends('layouts.store')
@section('title', config('shop.name').' — বাংলাদেশের জন্য প্রতিদিনের প্রয়োজনীয় পণ্য')
@section('content')
<section class="max-w-7xl mx-auto px-4 sm:px-6 pt-10 md:pt-20">
    <div class="grid md:grid-cols-2 gap-8 md:gap-12 items-center">
        <div class="animate-slide-up text-center md:text-left">
            <p class="text-xs tracking-[0.18em] uppercase text-clay font-medium">নতুন কালেকশন · ডেমো প্রিভিউ</p>
            <h1 class="font-display text-4xl sm:text-5xl lg:text-[3.6rem] leading-[1.05] tracking-tight mt-3">প্রতিদিনের জীবনের জন্য<br>আন্তরিক পণ্য।</h1>
            <p class="text-muted text-[17px] leading-relaxed mt-5 max-w-md mx-auto">রান্নাঘর, ঘর, ব্যক্তিগত যত্ন সহ প্রতিদিনের প্রয়োজনীয় পণ্যের বাছাই। টাকায় ন্যায্য দাম, ক্যাশ অন ডেলিভারি, সারা বাংলাদেশে ডেলিভারি।</p>
            <div class="flex flex-wrap gap-3 mt-8 justify-center md:justify-start">
                <a href="{{ route('products.index') }}" class="btn btn-primary btn-lg">সব পণ্য দেখুন</a>
                <a href="#featured" class="btn btn-outline btn-lg">নির্বাচিত পণ্য</a>
            </div>
            <dl class="grid grid-cols-3 gap-4 mt-10 border border-ink/10 rounded-xl p-4 bg-white/60">
                <div class="text-center">
                    <dt class="text-xs text-muted uppercase">ডেলিভারি</dt>
                    <dd class="font-semibold text-ink mt-0.5">ঢাকার ভিতরে ৳60</dd>
                </div>
                <div class="text-center border-l border-ink/5">
                    <dt class="text-xs text-muted uppercase">ঢাকার বাইরে</dt>
                    <dd class="font-semibold text-ink mt-0.5">৳120</dd>
                </div>
                <div class="text-center border-l border-ink/5">
                    <dt class="text-xs text-muted uppercase">পেমেন্ট</dt>
                    <dd class="font-semibold text-ink mt-0.5">ক্যাশ অন ডেলিভারি</dd>
                </div>
            </dl>
        </div>
        <div class="grid grid-cols-2 gap-3 md:gap-4">
            <img src="https://images.unsplash.com/photo-1556909212-d5b604d0c90d?w=700&q=80&auto=format&fit=crop" alt="বাছাই করা রান্নাঘরের পণ্য" class="rounded-xl aspect-4/5 object-cover w-full" fetchpriority="high" width="700" height="933" loading="eager" onerror="this.style.display='none'">
            <img src="https://images.unsplash.com/photo-1523381210434-271e8be1f52b?w=700&q=80&auto=format&fit=crop" alt="ভাঁজ করা দৈনন্দিন পোশাক" class="rounded-xl aspect-4/5 object-cover w-full mt-6 md:mt-0" width="700" height="933" loading="lazy" onerror="this.style.display='none'">
        </div>
    </div>
</section>
<section class="max-w-7xl mx-auto px-4 sm:px-6 mt-16 md:mt-24" aria-labelledby="cats">
    <div class="section-header">
        <div>
            <h2 id="cats" class="section-title">ক্যাটাগরি অনুযায়ী কিনুন</h2>
            <p class="section-subtitle">শুরু করার জন্য ছয়টি সেলাফ — ক্যাটালগ বাড়লে আরও যোগ হবে।</p>
        </div>
        <a href="{{ route('products.index') }}" class="text-sm text-forest underline underline-offset-4 hidden sm:inline">সব দেখুন</a>
    </div>
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4 mt-6">
        @foreach ($categories as $cat)
        <a href="{{ route('products.index', ['category' => $cat->slug]) }}" class="group block">
            <span class="block rounded-xl bg-linen aspect-square overflow-hidden border border-ink/5 transition-all">
                <img src="https://images.unsplash.com/photo-1441986300917-64674bd600d8?w=400&q=70&auto=format&fit=crop" alt="{{ $cat->name }}" loading="lazy" width="400" height="400" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105" onerror="this.style.display='none'">
            </span>
            <span class="block text-sm font-medium mt-2 text-ink group-hover:text-forest transition-colors">{{ $cat->name }}</span>
        </a>
        @endforeach
    </div>
</section>
<section id="featured" class="max-w-7xl mx-auto px-4 sm:px-6 mt-16 md:mt-24" aria-labelledby="feat">
    <div class="section-header">
        <div>
            <h2 id="feat" class="section-title">নির্বাচিত পণ্য</h2>
            <p class="section-subtitle">ডেমো প্রিভিউয়ের জন্য বিশেষভাবে বাছাই করা।</p>
        </div>
        <a href="{{ route('products.index') }}" class="text-sm text-forest underline underline-offset-4">সব পণ্য দেখুন</a>
    </div>
    @if ($featured->isEmpty())
    <div class="empty-state mt-6 animate-fade-in">
        <p class="empty-state-title">পণ্য যোগ করা হচ্ছে</p>
        <p class="empty-state-desc">শীঘ্রই আমাদের নির্বাচিত কালেকশন দেখুন।</p>
        <a href="{{ route('products.index') }}" class="mt-6 btn btn-primary btn-md">সব পণ্য দেখুন</a>
    </div>
    @else
    <div class="grid grid-cols-2 md:grid-cols-4 gap-x-4 gap-y-8 mt-6">
        @foreach ($featured as $product)
        @include('components.product-card', ['product' => $product])
        @endforeach
    </div>
    @endif
</section>
<section class="max-w-7xl mx-auto px-4 sm:px-6 mt-16 md:mt-24">
    <div class="grid md:grid-cols-2 gap-8 md:gap-12 items-center rounded-2xl border border-ink/10 bg-moss/50 p-6 sm:p-10 md:p-12">
        <div class="animate-slide-up text-center md:text-left" style="animation-delay:0.1s">
            <p class="inline-block text-[11px] tracking-[0.18em] uppercase font-semibold bg-forest text-white rounded-full px-3.5 py-1.5">ক্যাশ অন ডেলিভারি</p>
            <h2 class="font-display text-3xl md:text-4xl mt-4 leading-tight tracking-tight">অনলাইনে অর্ডার,<br>দরজায় পেমেন্ট।</h2>
            <p class="text-muted text-[15px] mt-3 max-w-sm mx-auto leading-relaxed">কোনো অগ্রিম পেমেন্ট নয়, কার্ডও লাগবে না। বাংলাদেশের যেকোনো প্রান্তে পার্চেল পান এবং পৌঁছালে নগদে পরিশোধ করুন।</p>
            <ul class="flex flex-wrap gap-2 mt-5 text-[13px] font-medium justify-center md:justify-start">
                <li class="inline-flex items-center gap-1.5 bg-white border border-ink/10 rounded-full px-3 py-1.5"><span class="text-clay">✓</span> কোনো অগ্রিম নয়</li>
                <li class="inline-flex items-center gap-1.5 bg-white border border-ink/10 rounded-full px-3 py-1.5"><span class="text-clay">✓</span> নগদে পরিশোধ</li>
                <li class="inline-flex items-center gap-1.5 bg-white border border-ink/10 rounded-full px-3 py-1.5"><span class="text-clay">✓</span> সারা দেশে ডেলিভারি</li>
            </ul>
            <a href="{{ route('products.index') }}" class="inline-block mt-7 btn btn-primary btn-md">কেনাকাটা শুরু করুন</a>
        </div>
        <div class="relative">
            <img src="https://images.unsplash.com/photo-1586880244406-556ebe35f282?w=900&q=80&auto=format&fit=crop" alt="ডেলিভারির জন্য প্রস্তুত পার্চেল" class="rounded-xl aspect-4/3 w-full object-cover shadow-md" loading="lazy" onerror="this.style.display='none'">
            <div class="absolute bottom-4 left-4 flex items-center gap-3 bg-white/95 backdrop-blur rounded-xl shadow-md px-4 py-3">
                <span class="w-10 h-10 rounded-full bg-moss text-forest grid place-items-center font-semibold">৳</span>
                <span class="text-sm leading-tight"><span class="block font-semibold">পৌঁছালে পেমেন্ট</span><span class="block text-muted text-[13px]">শুধু নগদ, কার্ড লাগবে না</span></span>
            </div>
        </div>
    </div>
</section>
<section class="max-w-7xl mx-auto px-4 sm:px-6 mt-16 md:mt-24" aria-labelledby="latest">
    <div class="section-header">
        <h2 id="latest" class="section-title">এইমাত্র এসেছে</h2>
    </div>
    @if ($latest->isEmpty())
    <div class="empty-state mt-6 animate-fade-in">
        <p class="empty-state-title">নতুন পণ্য এখানে দেখা যাবে</p>
        <p class="empty-state-desc">প্রতি সপ্তাহে নতুন পণ্য আসে।</p>
    </div>
    @else
    <div class="grid grid-cols-2 md:grid-cols-4 gap-x-4 gap-y-8 mt-6">
        @foreach ($latest as $product)
        @include('components.product-card', ['product' => $product])
        @endforeach
    </div>
    <div class="text-center mt-10">
        <a href="{{ route('products.index') }}" class="inline-block border border-forest text-forest font-medium px-8 py-3 rounded-md text-[15px] hover:bg-forest hover:text-white transition-colors">সব পণ্য দেখুন</a>
    </div>
    @endif
</section>

<section class="max-w-7xl mx-auto px-4 sm:px-6 mt-16 md:mt-24 pb-8" aria-label="অর্ডার কীভাবে করবেন">
    <div class="bg-sand border border-ink/10 rounded-2xl px-5 py-10 sm:px-8 md:px-12 md:py-14">
        <div class="text-center max-w-lg mx-auto">
            <p class="text-xs tracking-[0.18em] uppercase text-clay font-medium">সহজ ও স্বচ্ছ</p>
            <h2 class="font-display text-2xl md:text-3xl tracking-tight mt-2">অর্ডার কীভাবে করবেন</h2>
            <p class="text-muted text-[15px] mt-2.5">কার্ড নয়, অগ্রিম পেমেন্ট নয়। আজই অর্ডার করুন — পণ্য দরজায় পৌঁছালে মাত্র টাকা দিন।</p>
        </div>
        <ol class="grid gap-4 sm:grid-cols-3 mt-10">
            <li class="group bg-white border border-ink/10 rounded-xl p-6 transition-all hover:border-forest/25 hover:shadow-md">
                <div class="flex items-center justify-between">
                    <span class="w-11 h-11 rounded-xl bg-moss text-forest grid place-items-center">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="2" y="6" width="20" height="12" rx="2"/><circle cx="12" cy="12" r="2.6"/><path d="M6 12h.01M18 12h.01"/></svg>
                    </span>
                    <span class="text-[11px] font-semibold uppercase tracking-wider text-muted">ধাপ ১</span>
                </div>
                <h3 class="font-semibold mt-4">ক্যাশ অন ডেলিভারি</h3>
                <p class="text-sm text-muted mt-1.5 leading-relaxed">অর্ডার দরজায় পৌঁছালে মুহূর্তেই টাকা হাতে দিন — কার্ড নয়, অগ্রিম নয়।</p>
            </li>
            <li class="group bg-white border border-ink/10 rounded-xl p-6 transition-all hover:border-forest/25 hover:shadow-md">
                <div class="flex items-center justify-between">
                    <span class="w-11 h-11 rounded-xl bg-moss text-forest grid place-items-center">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="1" y="3" width="15" height="13" rx="1"/><path d="M16 8h4l3 3v5h-7z"/><circle cx="5.5" cy="18.5" r="2.2"/><circle cx="18.5" cy="18.5" r="2.2"/></svg>
                    </span>
                    <span class="text-[11px] font-semibold uppercase tracking-wider text-muted">ধাপ ২</span>
                </div>
                <h3 class="font-semibold mt-4">ঢাকা ও সারা দেশে ডেলিভারি</h3>
                <p class="text-sm text-muted mt-1.5 leading-relaxed">ঢাকার ভিতরে ফ্ল্যাট ৳60 ও বাইরে ৳120 — কনফার্ম করার আগে চেকআউটে সঠিক খরচ দেখাবে।</p>
            </li>
            <li class="group bg-white border border-ink/10 rounded-xl p-6 transition-all hover:border-forest/25 hover:shadow-md">
                <div class="flex items-center justify-between">
                    <span class="w-11 h-11 rounded-xl bg-moss text-forest grid place-items-center">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3M8.5 11l1.8 1.8 3.7-3.7"/></svg>
                    </span>
                    <span class="text-[11px] font-semibold uppercase tracking-wider text-muted">ধাপ ৩</span>
                </div>
                <h3 class="font-semibold mt-4">অর্ডার লুকআপ</h3>
                <p class="text-sm text-muted mt-1.5 leading-relaxed">রেফারেন্স নম্বর ও ফোন নম্বর দিয়ে যেকোনো সময় অর্ডার ট্র্যাক করুন।</p>
                <a href="{{ route('account.orders') }}" class="inline-flex items-center gap-1.5 text-sm text-forest font-medium mt-3 underline underline-offset-4 transition-all group-hover:gap-2.5">অর্ডার ট্র্যাক করুন
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                </a>
            </li>
        </ol>
    </div>
</section>
@endsection
