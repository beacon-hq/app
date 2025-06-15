<?php

declare(strict_types=1);

namespace App\Models;

use App\Values\PolicyDefinition;
use Bag\Bag;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 *
 *
 * @property string $id
 * @property string $application_id
 * @property string $environment_id
 * @property string $feature_flag_id
 * @property bool $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Bag|Collection|null $definition
 * @property-read Application $application
 * @property-read Environment $environment
 * @property-read FeatureFlag $featureFlag
 * @method static \Database\Factories\FeatureFlagStatusFactory factory($count = null, $state = [])
 * @method static Builder<static>|FeatureFlagStatus newModelQuery()
 * @method static Builder<static>|FeatureFlagStatus newQuery()
 * @method static Builder<static>|FeatureFlagStatus query()
 * @method static Builder<static>|FeatureFlagStatus whereApplication(string $application)
 * @method static Builder<static>|FeatureFlagStatus whereApplicationId($value)
 * @method static Builder<static>|FeatureFlagStatus whereCreatedAt($value)
 * @method static Builder<static>|FeatureFlagStatus whereDefinition($value)
 * @method static Builder<static>|FeatureFlagStatus whereEnvironment(string $environment)
 * @method static Builder<static>|FeatureFlagStatus whereEnvironmentId($value)
 * @method static Builder<static>|FeatureFlagStatus whereFeatureFlag(\App\Values\FeatureFlag $featureFlag)
 * @method static Builder<static>|FeatureFlagStatus whereFeatureFlagId($value)
 * @method static Builder<static>|FeatureFlagStatus whereId($value)
 * @method static Builder<static>|FeatureFlagStatus whereStatus($value)
 * @method static Builder<static>|FeatureFlagStatus whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class FeatureFlagStatus extends Model
{
    use HasFactory;
    use HasUlids;

    protected $fillable = [
        'application_id',
        'environment_id',
        'feature_flag_id',
        'definition',
        'status',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function environment(): BelongsTo
    {
        return $this->belongsTo(Environment::class);
    }

    public function featureFlag(): BelongsTo
    {
        return $this->belongsTo(FeatureFlag::class);
    }

    public function scopeWhereApplication(Builder $query, string $application): Builder
    {
        return $query->whereHas('application', fn (Builder $query) => $query->where('name', $application));
    }

    public function scopeWhereEnvironment(Builder $query, string $environment): Builder
    {
        return $query->whereHas('environment', fn (Builder $query) => $query->where('name', $environment));
    }

    public function scopeWhereFeatureFlag(Builder $query, FeatureFlag $featureFlag): Builder
    {
        if ($featureFlag->id !== null) {
            return $query->where('feature_flag_id', $featureFlag->id);
        }

        return $query->whereHas('featureFlag', fn (Builder $query) => $query->where('id', $featureFlag->id));
    }

    protected function casts(): array
    {
        return [
            'id' => 'string',
            'status' => 'boolean',
            'definition' => PolicyDefinition::castAsCollection(),
        ];
    }
}
