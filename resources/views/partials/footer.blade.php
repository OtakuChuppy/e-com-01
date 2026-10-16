<footer class="border-t border-ink/10 mt-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-12 grid gap-10 md:grid-cols-4">
        <div>
            <p class="font-display text-xl">{{ config('shop.name') }}</p>
            <p class="text-sm text-muted mt-2 max-w-xs">{{ config('shop.tagline') }}</p>
            <p class="text-xs text-muted mt-3">Demo storefront — sample data for preview.</p>
        </div>
        <nav aria-label="Shop">
            <p class="text-sm font-medium mb-3">Shop</p>
            <ul class="space-y-2 text-sm text-muted">
                <li><a class="hover:text-forest" href="{{ route('products.index') }}">All products</a></li>
                @foreach (($navCategories ?? collect())->take(5) as $cat)
                    <li><a class="hover:text-forest" href="{{ route('products.index', ['category' => $cat->slug]) }}">{{ $cat->name }}</a></li>
                @endforeach
            </ul>
        </nav>
        <nav aria-label="Help">
            <p class="text-sm font-medium mb-3">Help</p>
            <ul class="space-y-2 text-sm text-muted">
                <li><a class="hover:text-forest" href="{{ route('checkout.index') }}">Checkout</a></li>
                <li><a class="hover:text-forest" href="{{ route('account.orders') }}">Track your order</a></li>
                <li><a class="hover:text-forest" href="{{ route('policies.show', 'returns') }}">Returns &amp; cancellations</a></li>
                <li><a class="hover:text-forest" href="{{ route('policies.show', 'shipping') }}">Shipping info</a></li>
            </ul>
        </nav>
        <div>
            <p class="text-sm font-medium mb-3">Contact</p>
            <ul class="space-y-2 text-sm text-muted">
                <li>{{ config('shop.address') }}</li>
                <li><a class="hover:text-forest" href="tel:{{ config('shop.support_phone') }}">{{ config('shop.support_phone') }}</a></li>
                <li><a class="hover:text-forest" href="mailto:{{ config('shop.support_email') }}">{{ config('shop.support_email') }}</a></li>
            </ul>
        </div>
    </div>
    <div class="border-t border-ink/10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 py-4 flex flex-col sm:flex-row gap-2 justify-between text-xs text-muted">
            <p>© {{ date('Y') }} {{ config('shop.name') }}. All rights reserved.</p>
            <p>Prices in Bangladeshi Taka (৳). Cash on Delivery available.</p>
        </div>
    </div>
</footer>
