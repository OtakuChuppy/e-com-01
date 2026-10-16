<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function index(): View
    {
        $settings = Setting::orderBy('group')->orderBy('key')->get();

        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'delivery_inside_dhaka' => ['required', 'numeric', 'min:0', 'max:10000'],
            'delivery_outside_dhaka' => ['required', 'numeric', 'min:0', 'max:10000'],
            'meta_pixel_id' => ['nullable', 'string', 'max:30', 'regex:/^[0-9]{5,20}$/'],
            'gtm_container_id' => ['nullable', 'string', 'max:20', 'regex:/^GTM-[A-Z0-9]{4,12}$/'],
            'ga4_measurement_id' => ['nullable', 'string', 'max:20', 'regex:/^G-[A-Z0-9]{4,15}$/'],
            'robots_mode' => ['nullable', 'in:live,staging'],
        ]);

        foreach (['delivery_inside_dhaka', 'delivery_outside_dhaka'] as $k) {
            Setting::updateOrCreate(['key' => $k], ['value' => (string) $validated[$k], 'group' => 'shipping']);
        }
        foreach (['meta_pixel_id', 'gtm_container_id', 'ga4_measurement_id', 'robots_mode'] as $k) {
            Setting::updateOrCreate(['key' => $k], ['value' => $validated[$k] ?? null, 'group' => $k === 'robots_mode' ? 'seo' : 'tracking']);
        }

        AuditLog::create(['user_id' => $request->user()->id, 'action' => 'settings.update',
            'auditable_type' => Setting::class, 'auditable_id' => null,
            'metadata' => ['keys' => array_keys($validated)], 'ip_address' => $request->ip()]);

        return back()->with('success', 'Settings saved. Tracking IDs are validated, never raw scripts.');
    }
}
