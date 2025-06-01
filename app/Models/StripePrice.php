<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StripePrice extends Model
{
    use HasUlids;

    protected $fillable = [
        'stripe_id',
        'stripe_product_id',
        'name',
        'key',
        'unit_amount',
        'active',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(StripeProduct::class);
    }

    protected function casts(): array
    {
        return [
            'id' => 'string',
            'active' => 'boolean',
        ];
    }
}
