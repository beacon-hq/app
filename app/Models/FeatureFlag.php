<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\BelongsToTeam;
use App\Models\Traits\HasSlug;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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
 * @property-read FeatureFlagPolicy|null $pivot
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
    use HasUlids;
    use HasSlug;
    use HasFactory;
    use BelongsToTeam;

    protected $fillable = [
        'name',
        'description',
        'last_seen_at',
        'feature_type_id',
        'team_id',
    ];

    public function featureType(): BelongsTo
    {
        return $this->belongsTo(FeatureType::class);
    }

    public function policies(): BelongsToMany
    {
        return $this->belongsToMany(Policy::class)
            ->using(FeatureFlagPolicy::class)
            ->withPivot('order', 'values')
            ->orderByPivot('order', 'asc');
    }

    public function environments(): BelongsToMany
    {
        return $this->belongsToMany(Environment::class);
    }

    public function applications(): BelongsToMany
    {
        return $this->belongsToMany(Application::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class)->withTimestamps();
    }

    protected function casts(): array
    {
        return [
            'id' => 'string',
            'last_seen_at' => 'datetime',
        ];
    }
}
