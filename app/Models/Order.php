<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    public const STATUSES = ['pending', 'confirmed', 'on_hold', 'shipped', 'delivered', 'cancelled'];

    public const TRANSITIONS = [
        'pending' => ['confirmed', 'on_hold', 'cancelled'],
        'on_hold' => ['confirmed', 'cancelled'],
        'confirmed' => ['shipped', 'on_hold', 'cancelled'],
        'shipped' => ['delivered', 'cancelled'],
        'delivered' => [],
        'cancelled' => [],
    ];

    protected $fillable = ['reference', 'lookup_token', 'customer_id', 'user_id', 'customer_name', 'customer_phone', 'customer_email', 'address_line1', 'address_line2', 'area', 'district', 'postal_code', 'delivery_notes', 'subtotal', 'delivery_charge', 'discount', 'total', 'payment_method', 'payment_status', 'status', 'admin_note', 'placed_at'];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'delivery_charge' => 'decimal:2',
            'discount' => 'decimal:2',
            'total' => 'decimal:2',
            'placed_at' => 'datetime',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function history(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function canTransitionTo(string $to): bool
    {
        return in_array($to, self::TRANSITIONS[$this->status] ?? [], true);
    }
}
