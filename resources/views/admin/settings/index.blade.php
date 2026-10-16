@extends('layouts.admin')
@section('title', 'Settings — Admin')
@section('content')
<h1 class="font-display text-3xl tracking-tight">Settings</h1>
<p class="text-sm text-muted mt-1">Delivery charges apply at checkout. Tracking IDs are validated formats — raw scripts are never accepted.</p>
@php $g = fn($k) => $settings->firstWhere('key', $k)?->value; @endphp
<form action="{{ route('admin.settings.update') }}" method="POST" class="mt-5 grid sm:grid-cols-2 gap-4 bg-white border border-ink/10 rounded p-5">
@csrf @method('PATCH')
<div><label class="text-sm font-medium" for="delivery_inside_dhaka">Delivery inside Dhaka (৳) *</label><input id="delivery_inside_dhaka" name="delivery_inside_dhaka" type="number" step="0.01" min="0" required value="{{ old('delivery_inside_dhaka', $g('delivery_inside_dhaka') ?? 60) }}" class="mt-1 w-full border border-ink/15 rounded px-3 py-2 text-sm"></div>
<div><label class="text-sm font-medium" for="delivery_outside_dhaka">Delivery outside Dhaka (৳) *</label><input id="delivery_outside_dhaka" name="delivery_outside_dhaka" type="number" step="0.01" min="0" required value="{{ old('delivery_outside_dhaka', $g('delivery_outside_dhaka') ?? 120) }}" class="mt-1 w-full border border-ink/15 rounded px-3 py-2 text-sm"></div>
<div><label class="text-sm font-medium" for="meta_pixel_id">Meta Pixel ID (digits only)</label><input id="meta_pixel_id" name="meta_pixel_id" value="{{ old('meta_pixel_id', $g('meta_pixel_id')) }}" placeholder="e.g. 1234567890" class="mt-1 w-full border border-ink/15 rounded px-3 py-2 text-sm"></div>
<div><label class="text-sm font-medium" for="gtm_container_id">GTM container (GTM-XXXXXX)</label><input id="gtm_container_id" name="gtm_container_id" value="{{ old('gtm_container_id', $g('gtm_container_id')) }}" placeholder="GTM-ABC123" class="mt-1 w-full border border-ink/15 rounded px-3 py-2 text-sm"></div>
<div><label class="text-sm font-medium" for="ga4_measurement_id">GA4 ID (G-XXXXXX)</label><input id="ga4_measurement_id" name="ga4_measurement_id" value="{{ old('ga4_measurement_id', $g('ga4_measurement_id')) }}" placeholder="G-ABC123" class="mt-1 w-full border border-ink/15 rounded px-3 py-2 text-sm"></div>
<div><label class="text-sm font-medium" for="robots_mode">Robots mode</label><select id="robots_mode" name="robots_mode" class="mt-1 w-full border border-ink/15 rounded px-3 py-2 text-sm bg-white"><option value="staging" @selected(old('robots_mode', $g('robots_mode') ?? 'staging')==='staging')>Staging (disallow all)</option><option value="live" @selected(old('robots_mode', $g('robots_mode'))==='live')>Live (allow + sitemap)</option></select></div>
<div class="sm:col-span-2"><button class="bg-forest text-white text-sm font-medium px-6 py-2.5 rounded">Save settings</button></div>
</form>
<div class="bg-white border border-ink/10 rounded p-5 mt-4 text-sm">
<h2 class="font-medium">Courier integration (Steadfast — mock)</h2>
<p class="text-muted mt-1">Status: <span class="font-medium text-ink">not connected</span>. Live booking only after official docs + credentials + owner approval. Keys stay in server env, never in pages.</p>
</div>
@endsection
