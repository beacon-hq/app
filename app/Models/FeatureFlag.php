<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Pivot\FeatureFlagFeatureStatus;
use App\Models\Pivot\FeatureFlagStatusPolicy;
use App\Models\Traits\BelongsToTeam;
use App\Models\Traits\HasSlug;
use App\Values\FeatureType as FeatureTypeValue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Carbon;

/**
 *
 *
 * @property string $id
 * @property string $team_id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property Carbon|null $last_seen_at
 * @property string $feature_type_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, Application> $applications
 * @property-read int|null $applications_count
 * @property-read Collection<int, Environment> $environments
 * @property-read int|null $environments_count
 * @property-read FeatureType $featureType
 * @property-read FeatureFlagStatusPolicy|null $pivot
 * @property-read Collection<int, Policy> $policies
 * @property-read int|null $policies_count
 * @property-read Collection<int, Tag> $tags
 * @property-read int|null $tags_count
 * @property-read Team $team
 * @method static \Database\Factories\FeatureFlagFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeatureFlag newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeatureFlag newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeatureFlag query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeatureFlag whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeatureFlag whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeatureFlag whereFeatureTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeatureFlag whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeatureFlag whereLastSeenAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeatureFlag whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeatureFlag whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeatureFlag whereTeamId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeatureFlag whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class FeatureFlag extends Model
{
    use BelongsToTeam;
    use HasFactory;
    use HasSlug;
    use HasUlids;

    protected $fillable = [
        'name',
        'description',
        'last_seen_at',
        'feature_type_id',
        'team_id',
        'status',
    ];

    public function featureType(): BelongsTo
    {
        return $this->belongsTo(FeatureType::class);
    }

    public function environments(): HasManyThrough
    {
        return $this->hasManyThrough(
            Environment::class,
            FeatureFlagStatus::class,
            'feature_flag_id',
            'id',
            'id',
            'environment_id'
        );
    }

    public function applications(): HasManyThrough
    {
        return $this->hasManyThrough(
            Application::class,
            FeatureFlagStatus::class,
            'feature_flag_id',
            'id',
            'id',
            'application_id'
        );
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class)->withTimestamps();
    }

    public function statuses(): BelongsToMany
    {
        return $this->belongsToMany(FeatureFlagStatus::class)
            ->using(FeatureFlagFeatureStatus::class)
            ->withTimestamps();
    }

    public function scopeWhereEnvironment($query, string $environment): void
    {
        $query->whereHas('environments', function ($query) use ($environment) {
            $query->where('name', $environment);
        });
    }

    public function scopeWhereApplication($query, string $application): void
    {
        $query->whereHas('applications', function ($query) use ($application) {
            $query->where('name', $application);
        });
    }

    public function scopeWhereTags($query, iterable $tags): void
    {
        $query->whereHas('tags', function (Builder $query) use ($tags) {
            $query->whereIn('slug', \iterator_to_array($tags));
        });
    }

    public function scopeWhereName($query, string $name): void
    {
        $query->where('name', 'LIKE', "%$name%");
    }

    public function scopeWhereSlug($query, string $slug): void
    {
        $query->where('slug', $slug);
    }

    public function scopeWhereFeatureType($query, FeatureTypeValue $featureType): void
    {
        $query->whereHas('featureType', function ($query) use ($featureType) {
            $query->where('id', $featureType->id);
        });
    }

    protected function casts(): array
    {
        return [
            'id' => 'string',
            'last_seen_at' => 'datetime',
        ];
    }
}
