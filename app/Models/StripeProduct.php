<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StripeProduct extends Model
{
    use HasUlids;

    protected $fillable = [
        'stripe_id',
        'stripe_metered_price_id',
        'stripe_base_price_id',
        'name',
        'description',
        'active',
        'entitlements',
        'metadata',
        'order',
    ];

    public function meteredPrice(): BelongsTo
    {
        return $this->belongsTo(StripePrice::class, 'stripe_metered_price_id');
    }

    public function basePrice(): BelongsTo
    {
        return $this->belongsTo(StripePrice::class, 'stripe_base_price_id');
    }

    public function prices(): HasMany
    {
        return $this->hasMany(StripePrice::class, 'stripe_product_id');
    }

    protected function casts(): array
    {
        return [
            'id' => 'string',
            'active' => 'boolean',
            'entitlements' => 'array',
            'metadata' => 'array',
        ];
    }
}
