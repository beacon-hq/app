<?php

declare(strict_types=1);

namespace App\Models\Pivot;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 *
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeatureFlagFeatureStatus newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeatureFlagFeatureStatus newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeatureFlagFeatureStatus query()
 * @mixin \Eloquent
 */
class FeatureFlagFeatureStatus extends Pivot
{
    use HasUlids;

    protected $fillable = [
        'feature_flag_id',
        'feature_flag_status_id',
    ];
}
