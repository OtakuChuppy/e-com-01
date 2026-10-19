<footer class="mt-16 bg-forest text-white">
    @php $site = $siteSettings ?? []; @endphp
    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-10 md:py-16">
        <div class="grid grid-cols-2 gap-8 sm:gap-10 lg:grid-cols-12">
            <div class="col-span-2 lg:col-span-5">
                <div class="flex items-center gap-2.5">
                    <span class="w-9 h-9 rounded-full bg-white/10 border border-white/15 grid place-items-center font-display text-lg leading-none">{{ strtoupper(mb_substr($site['site_name'] ?? config('shop.name'), 0, 1)) }}</span>
                    <p class="font-display text-xl font-semibold tracking-tight">{{ $site['site_name'] ?? config('shop.name') }}</p>
                </div>
                <p class="text-sm text-white/60 leading-relaxed mt-4 max-w-sm">{{ $site['site_tagline'] ?? config('shop.tagline') }} রান্নাঘর, ঘর ও ব্যক্তিগত যত্নের পণ্য — সারা বাংলাদেশে ডেলিভারি, পৌঁছালে নগদে পরিশোধ।</p>
                @if ($site['footer_text'] ?? null)
                    <p class="text-xs text-white/40 mt-4">{{ $site['footer_text'] }}</p>
                @else
                    <p class="text-xs text-white/40 mt-4">ডেমো স্টোরফ্রন্ট — প্রিভিউয়ের জন্য নমুনা ডেটা।</p>
                @endif
                <div class="flex gap-2.5 mt-6">
                    <a href="{{ $site['facebook_url'] ?? '#' }}" class="w-9 h-9 rounded-lg border border-white/15 grid place-items-center text-white/70 hover:bg-white/10 hover:text-white transition-colors" aria-label="Facebook">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M13.5 21v-8h2.7l.4-3.1h-3.1V7.9c0-.9.3-1.5 1.6-1.5h1.7V3.6c-.3 0-1.3-.1-2.5-.1-2.5 0-4.2 1.5-4.2 4.3v2.1H7.4V13h2.7v8h3.4z"/></svg>
                    </a>
                    <a href="{{ $site['instagram_url'] ?? '#' }}" class="w-9 h-9 rounded-lg border border-white/15 grid place-items-center text-white/70 hover:bg-white/10 hover:text-white transition-colors" aria-label="Instagram">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.2" cy="6.8" r="1" fill="currentColor" stroke="none"/></svg>
                    </a>
                </div>
            </div>
        <nav class="lg:col-span-3" aria-label="কেনাকাটা">
            <p class="text-sm font-semibold mb-4">কেনাকাটা</p>
            <ul class="space-y-2.5 text-sm text-white/60">
                <li><a class="hover:text-white transition-colors" href="{{ route('products.index') }}">সব পণ্য</a></li>
                @foreach (($navCategories ?? collect())->take(5) as $cat)
                    <li><a class="hover:text-white transition-colors" href="{{ route('products.index', ['category' => $cat->slug]) }}">{{ $cat->name }}</a></li>
                @endforeach
            </ul>
        </nav>
        <nav class="lg:col-span-2" aria-label="সহায়তা">
            <p class="text-sm font-semibold mb-4">সহায়তা</p>
            <ul class="space-y-2.5 text-sm text-white/60">
                <li><a class="hover:text-white transition-colors" href="{{ route('checkout.index') }}">চেকআউট</a></li>
                <li><a class="hover:text-white transition-colors" href="{{ route('account.orders') }}">অর্ডার ট্র্যাক করুন</a></li>
                <li><a class="hover:text-white transition-colors" href="{{ route('policies.show', 'returns') }}">রিটার্ন ও বাতিলকরণ</a></li>
                <li><a class="hover:text-white transition-colors" href="{{ route('policies.show', 'shipping') }}">ডেলিভারি তথ্য</a></li>
            </ul>
        </nav>
        <div class="col-span-2 lg:col-span-2">
            <p class="text-sm font-semibold mb-4">যোগাযোগ</p>
            <ul class="space-y-3 text-sm text-white/60">
                <li>
                    <a href="tel:{{ $site['contact_phone'] ?? config('shop.support_phone') }}" class="inline-flex items-start gap-2 hover:text-white transition-colors">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="mt-0.5 shrink-0 text-white/40" aria-hidden="true"><path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1 1 .4 2 .7 2.9a2 2 0 0 1-.4 2.1L8.1 10a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.5c.9.4 1.9.6 2.9.7a2 2 0 0 1 1.6 2z"/></svg>
                        <span class="break-all">{{ $site['contact_phone'] ?? config('shop.support_phone') }}</span>
                    </a>
                </li>
                <li>
                    <a href="mailto:{{ $site['contact_email'] ?? config('shop.support_email') }}" class="inline-flex items-start gap-2 hover:text-white transition-colors">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="mt-0.5 shrink-0 text-white/40" aria-hidden="true"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m2 7 10 6L22 7"/></svg>
                        <span class="break-all">{{ $site['contact_email'] ?? config('shop.support_email') }}</span>
                    </a>
                </li>
                <li class="inline-flex items-start gap-2">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="mt-0.5 shrink-0 text-white/40" aria-hidden="true"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0z"/><circle cx="12" cy="10" r="3"/></svg>
                    <span class="break-all">{{ $site['address'] ?? config('shop.address') }}</span>
                </li>
            </ul>
        </div>
        </div>
        <ul class="mt-10 pt-8 border-t border-white/10 flex flex-wrap gap-x-8 gap-y-3 text-[13px] text-white/70">
            <li class="inline-flex items-center gap-2">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-white/40" aria-hidden="true"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10"/><path d="M9 12l2 2 4-4"/></svg>
                সারা দেশে ক্যাশ অন ডেলিভারি
            </li>
            <li class="inline-flex items-center gap-2">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-white/40" aria-hidden="true"><rect x="1" y="3" width="15" height="13" rx="1"/><path d="M16 8h4l3 3v5h-7z"/><circle cx="5.5" cy="18.5" r="2.2"/><circle cx="18.5" cy="18.5" r="2.2"/></svg>
                ঢাকার ভিতরে ৳60 · বাইরে ৳120
            </li>
            <li class="inline-flex items-center gap-2">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-white/40" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></svg>
                রেফারেন্স ও ফোনে অর্ডার লুকআপ
            </li>
        </ul>
    </div>
    <div class="border-t border-white/10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 py-6 flex flex-col sm:flex-row gap-3 justify-between text-xs text-white/50">
            <p>© {{ date('Y') }} {{ $site['site_name'] ?? config('shop.name') }}. সর্বস্বত্ব সংরক্ষিত।</p>
            <div class="flex flex-wrap gap-x-6 gap-y-2">
                <a href="{{ route('policies.show', 'privacy') }}" class="hover:text-white transition-colors">গোপনীয়তা</a>
                <a href="{{ route('policies.show', 'shipping') }}" class="hover:text-white transition-colors">ডেলিভারি</a>
                <a href="{{ route('policies.show', 'returns') }}" class="hover:text-white transition-colors">রিটার্ন</a>
                <span>মূল্য বাংলাদেশি টাকায়</span>
            </div>
        </div>
    </div>
</footer>
