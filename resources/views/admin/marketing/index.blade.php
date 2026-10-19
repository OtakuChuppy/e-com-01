@extends('layouts.admin')
@section('title', 'Marketing — Admin')
@section('content')
<h1 class="font-display text-3xl tracking-tight">Marketing</h1>
<p class="text-sm text-muted mt-1">Tracking (Meta Pixel, GTM, GA4) and SEO (robots mode + meta tags). Tracking IDs are validated formats — raw scripts are never accepted.</p>
@php $g = fn($k) => $settings->firstWhere('key', $k)?->value; @endphp
<form action="{{ route('admin.marketing.update') }}" method="POST" class="mt-5 grid sm:grid-cols-2 gap-4 bg-white border border-ink/10 rounded p-5">
@csrf @method('PATCH')

<div class="sm:col-span-2"><h2 class="font-medium text-ink mb-3 border-b border-ink/10 pb-1">Tracking</h2></div>
<div><label class="text-sm font-medium" for="meta_pixel_id">Meta Pixel ID (digits only)</label><input id="meta_pixel_id" name="meta_pixel_id" value="{{ old('meta_pixel_id', $g('meta_pixel_id')) }}" placeholder="e.g. 1234567890" class="mt-1 w-full border border-ink/15 rounded px-3 py-2 text-sm"></div>
<div><label class="text-sm font-medium" for="gtm_container_id">GTM container (GTM-XXXXXX)</label><input id="gtm_container_id" name="gtm_container_id" value="{{ old('gtm_container_id', $g('gtm_container_id')) }}" placeholder="GTM-ABC123" class="mt-1 w-full border border-ink/15 rounded px-3 py-2 text-sm"></div>
<div><label class="text-sm font-medium" for="ga4_measurement_id">GA4 ID (G-XXXXXX)</label><input id="ga4_measurement_id" name="ga4_measurement_id" value="{{ old('ga4_measurement_id', $g('ga4_measurement_id')) }}" placeholder="G-ABC123" class="mt-1 w-full border border-ink/15 rounded px-3 py-2 text-sm"></div>
<div><label class="text-sm font-medium" for="robots_mode">Robots mode (SEO)</label><select id="robots_mode" name="robots_mode" class="mt-1 w-full border border-ink/15 rounded px-3 py-2 text-sm bg-white"><option value="staging" @selected(old('robots_mode', $g('robots_mode') ?? 'staging')==='staging')>Staging (disallow all)</option><option value="live" @selected(old('robots_mode', $g('robots_mode'))==='live')>Live (allow + sitemap)</option></select></div>

<div class="sm:col-span-2"><h2 class="font-medium text-ink mb-3 border-b border-ink/10 pb-1 mt-6">SEO meta tags</h2></div>
<div><label class="text-sm font-medium" for="meta_title">Meta title</label><input id="meta_title" name="meta_title" value="{{ old('meta_title', $g('meta_title')) }}" placeholder="Shop name — category" class="mt-1 w-full border border-ink/15 rounded px-3 py-2 text-sm"></div>
<div><label class="text-sm font-medium" for="meta_description">Meta description</label><input id="meta_description" name="meta_description" value="{{ old('meta_description', $g('meta_description')) }}" placeholder="150-160 char description" class="mt-1 w-full border border-ink/15 rounded px-3 py-2 text-sm"></div>
<div><label class="text-sm font-medium" for="og_title">Open Graph title</label><input id="og_title" name="og_title" value="{{ old('og_title', $g('og_title')) }}" placeholder="Title shared on social" class="mt-1 w-full border border-ink/15 rounded px-3 py-2 text-sm"></div>
<div><label class="text-sm font-medium" for="og_description">Open Graph description</label><input id="og_description" name="og_description" value="{{ old('og_description', $g('og_description')) }}" placeholder="Description shared on social" class="mt-1 w-full border border-ink/15 rounded px-3 py-2 text-sm"></div>
<div><label class="text-sm font-medium" for="og_image">Open Graph image URL</label><input id="og_image" name="og_image" type="url" value="{{ old('og_image', $g('og_image')) }}" placeholder="https://example.com/og.png" class="mt-1 w-full border border-ink/15 rounded px-3 py-2 text-sm"></div>
<div><label class="text-sm font-medium" for="twitter_card">Twitter card type</label><select id="twitter_card" name="twitter_card" class="mt-1 w-full border border-ink/15 rounded px-3 py-2 text-sm bg-white"><option value="" @selected(!old('twitter_card', $g('twitter_card'))) Default (summary)</option><option value="summary" @selected(old('twitter_card', $g('twitter_card'))==='summary')>Summary</option><option value="summary_large_image" @selected(old('twitter_card', $g('twitter_card'))==='summary_large_image')>Summary with large image</option></select></div>
<div class="sm:col-span-2"><button class="bg-forest text-white text-sm font-medium px-6 py-2.5 rounded">Save marketing settings</button></div>
</form>
@endsection
