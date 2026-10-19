<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MarketingController extends Controller
{
    // Admin marketing/SEO settings: meta pixel, GTM, GA4, robots mode. Shudhumatro
    // validated ID save kora hoy, raw script nai — jar madhyome XSS jhuki thake na.
    // Owner ebong marketer access korte pare (admin na).

    public function index(): View
    {
        $settings = Setting::whereIn('key', ['meta_pixel_id', 'gtm_container_id', 'ga4_measurement_id', 'robots_mode'])
            ->orderBy('key')
            ->get();

        return view('admin.marketing.index', compact('settings'));
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'meta_pixel_id' => ['nullable', 'string', 'max:30', 'regex:/^[0-9]{5,20}$/'],
            'gtm_container_id' => ['nullable', 'string', 'max:20', 'regex:/^GTM-[A-Z0-9]{4,12}$/'],
            'ga4_measurement_id' => ['nullable', 'string', 'max:20', 'regex:/^G-[A-Z0-9]{4,15}$/'],
            'robots_mode' => ['nullable', 'in:live,staging'],

            // SEO meta — editable from the admin panel.
            'meta_title' => ['nullable', 'string', 'max:160'],
            'meta_description' => ['nullable', 'string', 'max:320'],
            'og_title' => ['nullable', 'string', 'max:160'],
            'og_description' => ['nullable', 'string', 'max:320'],
            'og_image' => ['nullable', 'url', 'max:255'],
            'twitter_card' => ['nullable', 'in:summary,summary_large_image'],
        ]);

        foreach (['meta_pixel_id', 'gtm_container_id', 'ga4_measurement_id'] as $k) {
            Setting::updateOrCreate(['key' => $k], ['value' => $validated[$k] ?? null, 'group' => 'tracking']);
        }
        Setting::updateOrCreate(['key' => 'robots_mode'], ['value' => $validated['robots_mode'] ?? null, 'group' => 'seo']);

        foreach (['meta_title', 'meta_description', 'og_title', 'og_description', 'og_image', 'twitter_card'] as $k) {
            Setting::updateOrCreate(['key' => $k], ['value' => $validated[$k] ?? null, 'group' => 'seo']);
        }

        AuditLog::create(['user_id' => $request->user()->id, 'action' => 'marketing.update',
            'auditable_type' => Setting::class, 'auditable_id' => null,
            'metadata' => ['keys' => array_keys($validated)], 'ip_address' => $request->ip()]);

        return back()->with('success', 'Marketing settings saved. Tracking IDs are validated, never raw scripts.');
    }
}
