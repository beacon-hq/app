<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Relations\Pivot;

class FeatureFlagPolicy extends Pivot
{
    use HasUlids;

    protected $fillable = [
        'feature_flag_id',
        'policy_id',
        'order',
        'values',
    ];

    protected $casts = [
        'order' => 'integer',
        'values' => 'array',
    ];
}
