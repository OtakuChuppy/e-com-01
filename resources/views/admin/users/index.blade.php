@extends('layouts.admin')
@section('title', 'Staff & roles — Admin')
@section('content')
<h1 class="font-display text-3xl tracking-tight">Staff & roles</h1>
<p class="text-sm text-muted mt-1">Owner: full access. Admin: products, orders, inventory, settings. Order manager: orders only. Last owner is protected.</p>
<div class="grid md:grid-cols-2 gap-4 mt-5">
    <div class="bg-white border border-ink/10 rounded p-5">
        <h2 class="font-medium">Create staff account</h2>
        <form action="{{ route('admin.users.store') }}" method="POST" class="mt-3 space-y-3">
            @csrf
            <div><label class="text-sm font-medium" for="name">Name *</label><input id="name" name="name" required maxlength="120" class="mt-1 w-full border border-ink/15 rounded px-3 py-2 text-sm"></div>
            <div><label class="text-sm font-medium" for="email">Email *</label><input id="email" name="email" type="email" required class="mt-1 w-full border border-ink/15 rounded px-3 py-2 text-sm"></div>
            <div class="grid grid-cols-2 gap-3">
                <div><label class="text-sm font-medium" for="password">Password (min 10 characters) *</label><input id="password" name="password" type="password" required minlength="10" autocomplete="new-password" class="mt-1 w-full border border-ink/15 rounded px-3 py-2 text-sm">
                    <p class="text-xs text-muted mt-1">At least 10 characters — required by the security policy.</p>
                </div>
                <div><label class="text-sm font-medium" for="password_confirmation">Confirm *</label><input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password" class="mt-1 w-full border border-ink/15 rounded px-3 py-2 text-sm"></div>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div><label class="text-sm font-medium" for="role">Role *</label><select id="role" name="role" class="mt-1 w-full border border-ink/15 rounded px-3 py-2 text-sm bg-white">
                        <option value="order_manager">Order manager</option>
                        <option value="admin">Admin</option>
                        <option value="owner">Owner</option>
                    </select></div>
                <div><label class="text-sm font-medium" for="phone">Phone</label><input id="phone" name="phone" maxlength="20" class="mt-1 w-full border border-ink/15 rounded px-3 py-2 text-sm"></div>
            </div>
            <button class="w-full bg-forest text-white text-sm font-medium px-4 py-2.5 rounded hover:bg-forest-deep transition-colors">＋ Add staff member</button>
        </form>
    </div>
    <div class="bg-white border border-ink/10 rounded p-5">
        <h2 class="font-medium">Accounts</h2>
        <ul class="mt-3 space-y-3 text-sm">
            @foreach($users as $usr)
            <li class="flex items-center justify-between gap-2 border-b border-ink/5 pb-3 last:border-0">
                <div>
                    <p class="font-medium">{{ $usr->name }}</p>
                    <p class="text-xs text-muted">{{ $usr->email }} · <span class="capitalize">{{ str_replace('_',' ',$usr->role) }}</span> · {{ $usr->is_active ? 'active' : 'disabled' }}</p>
                </div>
                <form action="{{ route('admin.users.update', $usr) }}" method="POST" class="flex gap-1 items-center">
                    @csrf @method('PATCH')
                    <select name="role" class="border border-ink/15 rounded px-2 py-1 text-xs bg-white">
                        <option value="owner" @selected($usr->role==='owner')>Owner</option>
                        <option value="admin" @selected($usr->role==='admin')>Admin</option>
                        <option value="order_manager" @selected($usr->role==='order_manager')>Order mgr</option>
                    </select>
                    <select name="is_active" class="border border-ink/15 rounded px-2 py-1 text-xs bg-white">
                        <option value="1" @selected($usr->is_active)>Active</option>
                        <option value="0" @selected(!$usr->is_active)>Disabled</option>
                    </select>
                    <button class="text-xs text-forest underline underline-offset-4">Save</button>
                </form>
            </li>
            @endforeach
        </ul>
        <div class="mt-3">{{ $users->links() }}</div>
    </div>
</div>
@endsection