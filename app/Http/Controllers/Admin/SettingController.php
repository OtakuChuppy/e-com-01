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
    // Admin delivery charge settings (inside/outside Dhaka). Owner ebong admin
    // edit korte parbe. Protiti update audit log kora hoyeche.

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

            // Site identity — editable from the admin panel.
            'site_name' => ['nullable', 'string', 'max:150'],
            'site_tagline' => ['nullable', 'string', 'max:255'],
            'site_url' => ['nullable', 'url', 'max:255'],
            'logo_url' => ['nullable', 'url', 'max:255'],
            'favicon_url' => ['nullable', 'url', 'max:255'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:255'],
            'footer_text' => ['nullable', 'string', 'max:255'],
            'facebook_url' => ['nullable', 'url', 'max:255'],
            'instagram_url' => ['nullable', 'url', 'max:255'],
        ]);

        $shipping = ['delivery_inside_dhaka', 'delivery_outside_dhaka'];
        $identity = [
            'site_name', 'site_tagline', 'site_url', 'logo_url', 'favicon_url',
            'contact_email', 'contact_phone', 'address', 'footer_text',
            'facebook_url', 'instagram_url',
        ];

        foreach ($shipping as $k) {
            Setting::updateOrCreate(['key' => $k], ['value' => (string) $validated[$k], 'group' => 'shipping']);
        }
        foreach ($identity as $k) {
            Setting::updateOrCreate(['key' => $k], ['value' => $validated[$k] ?? null, 'group' => 'identity']);
        }

        AuditLog::create(['user_id' => $request->user()->id, 'action' => 'settings.update',
            'auditable_type' => Setting::class, 'auditable_id' => null,
            'metadata' => ['keys' => array_keys($validated)], 'ip_address' => $request->ip()]);

        return back()->with('success', 'Settings saved.');
    }
}
