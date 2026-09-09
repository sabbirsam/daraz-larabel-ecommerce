<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute as CastAttribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Address extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'phone',
        'division',
        'district',
        'upazila',
        'address_line',
        'is_default_shipping',
        'is_default_billing',
        'type',
    ];

    protected function casts(): array
    {
        return [
            'is_default_shipping' => 'boolean',
            'is_default_billing' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected function fullAddress(): CastAttribute
    {
        return CastAttribute::make(
            get: fn () => implode(', ', array_filter([
                $this->address_line,
                $this->upazila,
                $this->district,
                $this->division,
            ]))
        );
    }
}
