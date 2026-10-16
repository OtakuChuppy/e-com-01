<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductVariant extends Model
{
    protected $fillable = ['product_id', 'name', 'sku', 'price_override', 'stock_quantity', 'attributes', 'is_active'];

    protected function casts(): array
    {
        return ['price_override' => 'decimal:2', 'attributes' => 'array', 'is_active' => 'boolean'];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function effectivePrice(): float
    {
        return (float) ($this->price_override ?? $this->product->base_price);
    }
}
