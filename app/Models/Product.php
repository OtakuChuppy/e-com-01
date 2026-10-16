<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Product extends Model
{
    protected $fillable = ['category_id', 'sku', 'slug', 'title', 'description', 'short_description', 'brand', 'status', 'base_price', 'compare_at_price', 'cost_price', 'stock_quantity', 'low_stock_threshold', 'weight_grams', 'is_featured', 'is_demo', 'meta_title', 'meta_description', 'canonical_url', 'published_at'];

    protected function casts(): array
    {
        return [
            'base_price' => 'decimal:2',
            'compare_at_price' => 'decimal:2',
            'cost_price' => 'decimal:2',
            'is_featured' => 'boolean',
            'is_demo' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Product $product) {
            if (empty($product->slug)) {
                $product->slug = Str::slug($product->title).'-'.strtolower(Str::random(5));
            }
        });
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function primaryImage(): ?ProductImage
    {
        return $this->images->firstWhere('is_primary', true) ?? $this->images->first();
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function getEffectivePriceAttribute(): string
    {
        return (string) $this->base_price;
    }

    public function getDiscountPercentAttribute(): ?int
    {
        if (! $this->compare_at_price || (float) $this->compare_at_price <= (float) $this->base_price) {
            return null;
        }

        return (int) round(((float) $this->compare_at_price - (float) $this->base_price) / (float) $this->compare_at_price * 100);
    }

    public function getInStockAttribute(): bool
    {
        if ($this->variants()->exists()) {
            return $this->variants()->where('is_active', true)->where('stock_quantity', '>', 0)->exists();
        }

        return $this->stock_quantity > 0;
    }
}
