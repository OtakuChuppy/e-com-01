@extends('layouts.admin')
@section('title', 'Settings — Admin')
@section('content')
<h1 class="font-display text-3xl tracking-tight">Settings</h1>
<p class="text-sm text-muted mt-1">Delivery charges apply at checkout. Site identity (name, links, contacts) below. Tracking & SEO now live under Marketing (owner + marketer only).</p>
@php $g = fn($k) => $settings->firstWhere('key', $k)?->value; @endphp
<form action="{{ route('admin.settings.update') }}" method="POST" class="mt-5 grid sm:grid-cols-2 gap-4 bg-white border border-ink/10 rounded p-5">
@csrf @method('PATCH')

<div class="sm:col-span-2"><h2 class="font-medium text-ink mb-3 border-b border-ink/10 pb-1">Delivery charges</h2></div>
<div><label class="text-sm font-medium" for="delivery_inside_dhaka">Delivery inside Dhaka (৳) *</label><input id="delivery_inside_dhaka" name="delivery_inside_dhaka" type="number" step="0.01" min="0" required value="{{ old('delivery_inside_dhaka', $g('delivery_inside_dhaka') ?? 60) }}" class="mt-1 w-full border border-ink/15 rounded px-3 py-2 text-sm"></div>
<div><label class="text-sm font-medium" for="delivery_outside_dhaka">Delivery outside Dhaka (৳) *</label><input id="delivery_outside_dhaka" name="delivery_outside_dhaka" type="number" step="0.01" min="0" required value="{{ old('delivery_outside_dhaka', $g('delivery_outside_dhaka') ?? 120) }}" class="mt-1 w-full border border-ink/15 rounded px-3 py-2 text-sm"></div>

<div class="sm:col-span-2"><h2 class="font-medium text-ink mb-3 border-b border-ink/10 pb-1 mt-6">Site identity</h2></div>
<div><label class="text-sm font-medium" for="site_name">Site name *</label><input id="site_name" name="site_name" value="{{ old('site_name', $g('site_name')) }}" placeholder="e.g. DeshiMart" class="mt-1 w-full border border-ink/15 rounded px-3 py-2 text-sm"></div>
<div><label class="text-sm font-medium" for="site_tagline">Tagline</label><input id="site_tagline" name="site_tagline" value="{{ old('site_tagline', $g('site_tagline')) }}" placeholder="Everyday essentials, honestly priced." class="mt-1 w-full border border-ink/15 rounded px-3 py-2 text-sm"></div>
<div><label class="text-sm font-medium" for="site_url">Site URL</label><input id="site_url" name="site_url" type="url" value="{{ old('site_url', $g('site_url')) }}" placeholder="https://example.com" class="mt-1 w-full border border-ink/15 rounded px-3 py-2 text-sm"></div>
<div><label class="text-sm font-medium" for="logo_url">Logo URL</label><input id="logo_url" name="logo_url" type="url" value="{{ old('logo_url', $g('logo_url')) }}" placeholder="https://example.com/logo.png" class="mt-1 w-full border border-ink/15 rounded px-3 py-2 text-sm"></div>
<div><label class="text-sm font-medium" for="favicon_url">Favicon URL</label><input id="favicon_url" name="favicon_url" type="url" value="{{ old('favicon_url', $g('favicon_url')) }}" placeholder="https://example.com/favicon.ico" class="mt-1 w-full border border-ink/15 rounded px-3 py-2 text-sm"></div>
<div><label class="text-sm font-medium" for="contact_email">Contact email</label><input id="contact_email" name="contact_email" type="email" value="{{ old('contact_email', $g('contact_email')) }}" placeholder="hello@example.com" class="mt-1 w-full border border-ink/15 rounded px-3 py-2 text-sm"></div>
<div><label class="text-sm font-medium" for="contact_phone">Contact phone</label><input id="contact_phone" name="contact_phone" type="tel" value="{{ old('contact_phone', $g('contact_phone')) }}" placeholder="09600-000000" class="mt-1 w-full border border-ink/15 rounded px-3 py-2 text-sm"></div>
<div><label class="text-sm font-medium" for="address">Address</label><input id="address" name="address" value="{{ old('address', $g('address')) }}" placeholder="Dhaka, Bangladesh" class="mt-1 w-full border border-ink/15 rounded px-3 py-2 text-sm"></div>
<div><label class="text-sm font-medium" for="footer_text">Footer text</label><input id="footer_text" name="footer_text" value="{{ old('footer_text', $g('footer_text')) }}" placeholder="Copyright note..." class="mt-1 w-full border border-ink/15 rounded px-3 py-2 text-sm"></div>
<div><label class="text-sm font-medium" for="facebook_url">Facebook URL</label><input id="facebook_url" name="facebook_url" type="url" value="{{ old('facebook_url', $g('facebook_url')) }}" placeholder="https://facebook.com/shop" class="mt-1 w-full border border-ink/15 rounded px-3 py-2 text-sm"></div>
<div><label class="text-sm font-medium" for="instagram_url">Instagram URL</label><input id="instagram_url" name="instagram_url" type="url" value="{{ old('instagram_url', $g('instagram_url')) }}" placeholder="https://instagram.com/shop" class="mt-1 w-full border border-ink/15 rounded px-3 py-2 text-sm"></div>
<div class="sm:col-span-2"><button class="bg-forest text-white text-sm font-medium px-6 py-2.5 rounded">Save settings</button></div>
</form>
<div class="bg-white border border-ink/10 rounded p-5 mt-4 text-sm">
<h2 class="font-medium">Courier integration (Steadfast — mock)</h2>
<p class="text-muted mt-1">Status: <span class="font-medium text-ink">not connected</span>. Live booking only after official docs + credentials + owner approval. Keys stay in server env, never in pages.</p>
</div>
@endsection
