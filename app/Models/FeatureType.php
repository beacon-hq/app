<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\BelongsToTeam;
use App\Models\Traits\HasSlug;
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
 * @property string|null $description
 * @property bool $temporary
 * @property string $color
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $icon
 * @property-read Team $team
 * @method static \Database\Factories\FeatureTypeFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeatureType newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeatureType newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeatureType query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeatureType whereColor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeatureType whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeatureType whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeatureType whereIcon($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeatureType whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeatureType whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeatureType whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeatureType whereTemporary($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeatureType whereTeamId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeatureType whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class FeatureType extends Model
{
    use HasFactory;
    use HasSlug;
    use HasUlids;
    use BelongsToTeam;

    protected $fillable = [
        'name',
        'description',
        'temporary',
        'color',
        'icon',
        'team_id',
    ];

    protected function casts(): array
    {
        return [
            'id' => 'string',
            'temporary' => 'boolean',
        ];
    }
}
