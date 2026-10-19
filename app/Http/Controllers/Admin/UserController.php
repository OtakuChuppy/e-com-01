<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    // Admin user management: create, update staff accounts. Shudhumatro owner
    // user create/update korte parbe. Last owner deactivate/demote rodh kora hoyeche.

    public function index(): View
    {
        $users = User::orderBy('name')->paginate(15);

        return view('admin.users.index', compact('users'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190', 'unique:users,email'],
            'password' => ['required', 'string', 'min:10', 'max:72', 'confirmed'],
            'role' => ['required', Rule::in(User::ROLES)],
            'phone' => ['nullable', 'string', 'max:20'],
        ]);

        $user = User::create($validated + ['is_active' => true]);
        AuditLog::create(['user_id' => $request->user()->id, 'action' => 'user.create',
            'auditable_type' => User::class, 'auditable_id' => $user->id,
            'metadata' => ['email' => $user->email, 'role' => $user->role], 'ip_address' => $request->ip()]);

        return back()->with('success', "Staff account {$user->email} created ({$user->role}).");
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        // Never allow removing/demoting the last owner or self-demotion lockout.
        $validated = $request->validate([
            'role' => ['required', Rule::in(User::ROLES)],
            'is_active' => ['required', 'boolean'],
        ]);

        if ($user->id === $request->user()->id && ($validated['role'] !== 'owner' || ! $validated['is_active'])) {
            return back()->with('error', 'You cannot demote or deactivate your own owner account.');
        }

        if ($user->role === 'owner' && $validated['role'] !== 'owner') {
            $otherOwners = User::where('role', 'owner')->where('id', '!=', $user->id)->where('is_active', true)->count();
            if ($otherOwners === 0) {
                return back()->with('error', 'Cannot demote the last active owner.');
            }
        }

        if (! $validated['is_active'] && $user->role === 'owner') {
            $otherOwners = User::where('role', 'owner')->where('id', '!=', $user->id)->where('is_active', true)->count();
            if ($otherOwners === 0) {
                return back()->with('error', 'Cannot deactivate the last active owner.');
            }
        }

        $user->update($validated);
        AuditLog::create(['user_id' => $request->user()->id, 'action' => 'user.update',
            'auditable_type' => User::class, 'auditable_id' => $user->id, 'metadata' => $validated, 'ip_address' => $request->ip()]);

        return back()->with('success', "Account {$user->email} updated.");
    }
}
