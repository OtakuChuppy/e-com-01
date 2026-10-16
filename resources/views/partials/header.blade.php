<header class="sticky top-0 z-40 bg-ivory/95 backdrop-blur border-b border-ink/10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6">
        <div class="flex items-center justify-between h-16 gap-4">
            <div class="flex items-center gap-3">
                <button class="md:hidden p-2 -ml-2" data-open-menu aria-label="Open menu" aria-expanded="false">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
                </button>
                <a href="{{ route('home') }}" class="flex items-center gap-2" aria-label="{{ config('shop.name') }} home">
                    <span class="w-8 h-8 rounded-full bg-forest text-white grid place-items-center font-display text-lg leading-none">D</span>
                    <span class="font-display text-xl tracking-tight">{{ config('shop.name') }}</span>
                </a>
            </div>

            <nav class="hidden md:flex items-center gap-7 text-[15px]" aria-label="Primary">
                <a href="{{ route('products.index') }}" class="hover:text-forest">Shop all</a>
                @foreach ($navCategories as $cat)
                    <a href="{{ route('products.index', ['category' => $cat->slug]) }}" class="hover:text-forest">{{ $cat->name }}</a>
                @endforeach
            </nav>

            <div class="flex items-center gap-1 sm:gap-2">
                <form action="{{ route('products.index') }}" method="GET" class="hidden sm:flex items-center" role="search">
                    <label for="q" class="sr-only">Search products</label>
                    <input id="q" name="q" value="{{ request('q') }}" placeholder="Search…" class="w-36 lg:w-48 bg-transparent border-b border-ink/20 focus:border-forest text-sm py-1 px-1 placeholder:text-muted focus:outline-none">
                </form>
                <a href="{{ route('account.orders') }}" class="p-2 hover:text-forest" aria-label="Account and orders">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="12" cy="8" r="3.5"/><path d="M5 20c1.2-3.4 4-5 7-5s5.8 1.6 7 5"/></svg>
                </a>
                <button class="p-2 hover:text-forest relative" data-open-cart aria-label="Open cart">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M6 8h15l-1.5 8.5a1 1 0 0 1-1 .5H8.7a1 1 0 0 1-1-.8L5 4.5A1 1 0 0 0 4 4H2"/><circle cx="9.5" cy="20" r="1.3"/><circle cx="17" cy="20" r="1.3"/></svg>
                    @if (($cartCount ?? 0) > 0)
                        <span class="absolute top-0.5 right-0.5 min-w-4 h-4 px-1 rounded-full bg-clay text-white text-[10px] grid place-items-center">{{ $cartCount }}</span>
                    @endif
                </button>
            </div>
        </div>
    </div>

    <div id="mobile-menu" class="hidden md:hidden border-t border-ink/10" x-data="{ open: false }">
        <nav class="px-4 py-3 space-y-1 text-[15px]" aria-label="Mobile">
            <a href="{{ route('products.index') }}" class="block py-2">Shop all</a>
            @foreach ($navCategories as $cat)
                <a href="{{ route('products.index', ['category' => $cat->slug]) }}" class="block py-2">{{ $cat->name }}</a>
            @endforeach
            <form action="{{ route('products.index') }}" method="GET" class="pt-2" role="search">
                <label for="mq" class="sr-only">Search products</label>
                <input id="mq" name="q" placeholder="Search products…" class="w-full border border-ink/15 rounded px-3 py-2 text-sm bg-white">
            </form>
        </nav>
    </div>
</header>
<script>
document.querySelector('[data-open-menu]')?.addEventListener('click', (e) => {
    const m = document.getElementById('mobile-menu');
    m.classList.toggle('hidden');
    e.currentTarget.setAttribute('aria-expanded', String(!m.classList.contains('hidden')));
});
</script>
