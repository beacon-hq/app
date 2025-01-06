<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\BelongsToTeam;
use App\Models\Traits\HasSlug;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 *
 *
 * @property string $id
 * @property string $team_id
 * @property string $name
 * @property string $slug
 * @property string $display_name
 * @property string|null $description
 * @property string $color
 * @property Carbon $last_seen_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read mixed $environments
 * @property-read Team $team
 * @method static \Database\Factories\ApplicationFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Application newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Application newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Application query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Application whereColor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Application whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Application whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Application whereDisplayName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Application whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Application whereLastSeenAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Application whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Application whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Application whereTeamId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Application whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Application extends Model
{
    use HasFactory;
    use HasSlug;
    use HasUlids;
    use BelongsToTeam;

    protected $fillable = [
        'name',
        'display_name',
        'description',
        'last_seen_at',
        'color',
        'team_id',
    ];

    protected function casts(): array
    {
        return [
            'id' => 'string',
            'last_seen_at' => 'datetime',
        ];
    }

    public function environments(): Attribute
    {
        return Attribute::make(get: function () {
            $query = Environment::query()
                ->select('environments.*')
                ->from('feature_flags')
                ->join('application_feature_flag', 'feature_flags.id', '=', 'application_feature_flag.feature_flag_id')
                ->join('environment_feature_flag', 'feature_flags.id', '=', 'environment_feature_flag.feature_flag_id')
                ->join('environments', 'environments.id', '=', 'environment_feature_flag.environment_id')
                ->where('application_feature_flag.application_id', $this->id)
                ->groupBy(['environments.id', 'environments.name'])
                ->orderBy('environments.name');

            return $query
                ->get();
        });
    }
}
