<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    // Order-er ekti line item: product, variant, unit price, quantity, line total.
    // attributes cast holo array (product option snapshot).

    protected $fillable = ['order_id', 'product_id', 'product_variant_id', 'name', 'sku', 'variant_name', 'unit_price', 'quantity', 'line_total', 'attributes'];

    protected function casts(): array
    {
        return ['unit_price' => 'decimal:2', 'line_total' => 'decimal:2', 'attributes' => 'array'];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }
}
