<?php

declare(strict_types=1);

namespace App\Models\Pivot;

use App\Values\PolicyValue;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Carbon;

/**
 *
 *
 * @property string $id
 * @property string $feature_flag_id
 * @property string $policy_id
 * @property int $order
 * @property array<array-key, mixed> $values
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeatureFlagStatusPolicy newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeatureFlagStatusPolicy newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeatureFlagStatusPolicy query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeatureFlagStatusPolicy whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeatureFlagStatusPolicy whereFeatureFlagId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeatureFlagStatusPolicy whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeatureFlagStatusPolicy whereOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeatureFlagStatusPolicy wherePolicyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeatureFlagStatusPolicy whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeatureFlagStatusPolicy whereValues($value)
 * @mixin \Eloquent
 */
class FeatureFlagStatusPolicy extends Pivot
{
    use HasUlids;

    protected $fillable = [
        'feature_flag_status_id',
        'policy_id',
        'order',
        'values',
    ];

    protected function casts(): array
    {
        return [
            'order' => 'integer',
            'values' => PolicyValue::castAsCollection(),
        ];
    }
}
