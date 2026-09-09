<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute as CastAttribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'brand_id',
        'title',
        'slug',
        'sku',
        'price',
        'sale_price',
        'stock',
        'short_description',
        'description',
        'specifications',
        'is_active',
        'is_featured',
        'rating_avg',
        'reviews_count',
        'sold_count',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'sale_price' => 'decimal:2',
            'stock' => 'integer',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'rating_avg' => 'decimal:2',
            'reviews_count' => 'integer',
            'sold_count' => 'integer',
            'specifications' => 'array',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function primaryImage(): HasOne
    {
        return $this->hasOne(ProductImage::class)->where('is_primary', true);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class)->where('status', 'approved');
    }

    public function allReviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    // Accessors
    protected function effectivePrice(): CastAttribute
    {
        return CastAttribute::make(
            get: fn () => $this->sale_price !== null && $this->sale_price < $this->price
                ? (float) $this->sale_price
                : (float) $this->price,
        );
    }

    protected function discountPercentage(): CastAttribute
    {
        return CastAttribute::make(
            get: function () {
                if ($this->sale_price && $this->sale_price < $this->price && $this->price > 0) {
                    return (int) round((($this->price - $this->sale_price) / $this->price) * 100);
                }
                return 0;
            }
        );
    }

    protected function inStock(): CastAttribute
    {
        return CastAttribute::make(
            get: fn () => $this->stock > 0,
        );
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true)->where('is_active', true);
    }
}
