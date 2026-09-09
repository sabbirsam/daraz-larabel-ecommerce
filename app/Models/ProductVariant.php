<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute as CastAttribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductVariant extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'sku',
        'price',
        'sale_price',
        'stock',
        'image_path',
        'attributes',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'sale_price' => 'decimal:2',
            'stock' => 'integer',
            'attributes' => 'array',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    protected function effectivePrice(): CastAttribute
    {
        return CastAttribute::make(
            get: function () {
                if ($this->sale_price !== null && $this->sale_price > 0) {
                    return (float) $this->sale_price;
                }
                if ($this->price !== null && $this->price > 0) {
                    return (float) $this->price;
                }
                return (float) ($this->product?->effective_price ?? 0);
            }
        );
    }

    protected function title(): CastAttribute
    {
        return CastAttribute::make(
            get: function ($value, array $attributes) {
                $raw = $attributes['attributes'] ?? null;
                $decoded = is_string($raw) ? json_decode($raw, true) : (is_array($raw) ? $raw : []);
                if (! empty($decoded) && is_array($decoded)) {
                    return implode(', ', array_map(
                        fn ($k, $v) => is_numeric($k) ? $v : "{$v}",
                        array_keys($decoded),
                        array_values($decoded)
                    ));
                }
                return $attributes['sku'] ?? '';
            }
        );
    }
}

