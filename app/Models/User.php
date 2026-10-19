<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role', 'phone', 'is_active'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    // Staff user model with role-based access control (RBAC). Roles: owner, admin,
    // order_manager, marketer. canManage* methods permission check kore.
    // is_active false thaka user login korte pare na.

    public const ROLES = ['owner', 'admin', 'order_manager', 'marketer'];

    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function isOwner(): bool
    {
        return $this->role === 'owner';
    }

    public function canManageProducts(): bool
    {
        return in_array($this->role, ['owner', 'admin'], true);
    }

    public function canManageOrders(): bool
    {
        return in_array($this->role, ['owner', 'admin', 'order_manager'], true);
    }

    /**
     * Deleting an order is irreversible — owners and admins only (mirrors canManageProducts).
     */
    public function canDeleteOrders(): bool
    {
        return in_array($this->role, ['owner', 'admin'], true);
    }

    /**
     * Marketing (tracking IDs, SEO/robots) — owners and marketers only, never plain admins.
     */
    public function canManageMarketing(): bool
    {
        return in_array($this->role, ['owner', 'marketer'], true);
    }

    public function canManageInventory(): bool
    {
        return in_array($this->role, ['owner', 'admin'], true);
    }

    public function canManageUsers(): bool
    {
        return $this->role === 'owner';
    }

    public function canManageSettings(): bool
    {
        return in_array($this->role, ['owner', 'admin'], true);
    }
}
