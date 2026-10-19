<header class="sticky top-0 z-40 bg-ivory/90 backdrop-blur-md border-b border-ink/10">
    @php $site = $siteSettings ?? []; @endphp
    <div class="max-w-7xl mx-auto px-4 sm:px-6">
        <div class="flex items-center justify-between h-16 gap-4">
            <div class="flex items-center gap-3">
                <button data-open-menu aria-label="মেনু খুলুন" aria-expanded="false" class="md:hidden p-2 -ml-2 text-ink hover:text-forest transition-colors">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
                </button>
                <a href="{{ route('home') }}" class="flex items-center gap-2" aria-label="{{ ($site['site_name'] ?? config('shop.name', config('app.name'))) }} home">
                    <span class="w-8 h-8 rounded-full bg-forest text-white grid place-items-center font-display text-lg leading-none">D</span>
                    <span class="font-display text-xl tracking-tight">{{ $site['site_name'] ?? config('shop.name', config('app.name')) }}</span>
                </a>
            </div>
            <nav class="hidden md:flex items-center gap-6 text-[15px]" aria-label="প্রধান নেভিগেশন">
                <a href="{{ route('products.index') }}" class="text-muted hover:text-forest transition-colors">সব পণ্য</a>
                <a href="{{ route('account.orders') }}" class="text-muted hover:text-forest transition-colors" aria-label="অর্ডার ট্র্যাক করুন">অর্ডার ট্র্যাক</a>
            </nav>
            <div class="flex items-center gap-2 sm:gap-3">
                <form action="{{ route('products.index') }}" method="GET" class="hidden sm:flex items-center" role="search">
                    <label for="q" class="sr-only">পণ্য খুঁজুন</label>
                    <div class="relative">
                        <input id="q" name="q" value="{{ request('q') }}" placeholder="পণ্য খুঁজুন…" class="w-40 lg:w-48 bg-white border border-ink/15 rounded-md px-3 py-2 text-sm focus:border-forest focus:ring-1 focus:ring-forest outline-none transition-all">
                        <button type="submit" class="absolute right-2 top-1/2 -translate-y-1/2 text-muted hover:text-forest transition-colors">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.3-4.3"/></svg>
                        </button>
                    </div>
                </form>
                <a href="{{ route('account.orders') }}" class="p-2 text-muted hover:text-forest transition-colors" aria-label="অ্যাকাউন্ট ও অর্ডার">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="12" cy="8" r="3.5"/><path d="M5 20c1.2-3.4 4-5 7-5s5.8 1.6 7 5"/></svg>
                </a>
                <button data-open-cart class="p-2 relative text-muted hover:text-forest transition-colors" aria-label="কার্ট খুলুন" aria-haspopup="dialog">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M6 8h15l-1.5 8.5a1 1 0 0 1-1 .5H8.7a1 1 0 0 1-1-.8L5 4.5A1 1 0 0 0 4 4H2"/><circle cx="9.5" cy="20" r="1.3"/><circle cx="17" cy="20" r="1.3"/></svg>
                    @if (($cartCount ?? 0) > 0)
                        <span class="absolute -top-0.5 -right-0.5 min-w-4.5 h-4.5 px-1 rounded-full bg-clay text-white text-[10px] leading-none grid place-items-center shadow-sm border border-ivory">{{ $cartCount }}</span>
                    @endif
                </button>
            </div>
        </div>
    </div>

    <div id="mobile-menu" class="hidden md:hidden border-t border-ink/10 bg-ivory">
        <div class="px-4 py-4">
            <button data-close-menu class="mb-4 w-full flex items-center justify-between text-sm font-medium text-ink">
                <span>মেনু</span>
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6l-12 12M6 6l12 12"/></svg>
            </button>
            <nav class="px-2 space-y-1 text-[15px]" aria-label="মোবাইল নেভিগেশন">
                <a href="{{ route('products.index') }}" class="block py-2.5 px-3 rounded-md text-ink hover:bg-linen hover:text-forest transition-colors">সব পণ্য</a>
                @foreach ($navCategories as $cat)
                    <a href="{{ route('products.index', ['category' => $cat->slug]) }}" class="block py-2.5 px-3 rounded-md transition-colors {{ request('category') === $cat->slug ? 'bg-moss text-forest font-medium' : 'text-ink hover:bg-linen hover:text-forest' }}">{{ $cat->name }}</a>
                @endforeach
                <a href="{{ route('account.orders') }}" class="block py-2.5 px-3 rounded-md text-ink hover:bg-linen hover:text-forest transition-colors">অর্ডার ট্র্যাক করুন</a>
            </nav>
            <form action="{{ route('products.index') }}" method="GET" class="mt-4 pt-4 border-t border-ink/10" role="search">
                <label for="mq" class="sr-only">পণ্য খুঁজুন</label>
                <input id="mq" name="q" placeholder="পণ্য খুঁজুন…" class="w-full border border-ink/15 rounded-md px-3 py-2.5 text-sm bg-white focus:border-forest focus:ring-1 focus:ring-forest outline-none">
            </form>
        </div>
    </div>
</header>
<script>
const menuBtn = document.querySelector('[data-open-menu]');
const mobileMenu = document.getElementById('mobile-menu');
function setMobileMenu(open) {
    if (!mobileMenu) return;
    mobileMenu.classList.toggle('hidden', !open);
    menuBtn?.setAttribute('aria-expanded', String(open));
    document.body.style.overflow = open ? 'hidden' : '';
}
menuBtn?.addEventListener('click', () => setMobileMenu(mobileMenu.classList.contains('hidden')));
document.querySelector('[data-close-menu]')?.addEventListener('click', () => setMobileMenu(false));
</script>
