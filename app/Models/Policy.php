<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\BelongsToTeam;
use App\Models\Traits\HasSlug;
use App\Values\PolicyDefinition;
use Bag\Bag;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 *
 *
 * @property string $id
 * @property string $team_id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property Bag|Collection $definition
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Team $team
 * @method static \Database\Factories\PolicyFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Policy newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Policy newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Policy query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Policy whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Policy whereDefinition($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Policy whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Policy whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Policy whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Policy whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Policy whereTeamId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Policy whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Policy extends Model
{
    use BelongsToTeam;
    use HasFactory;
    use HasSlug;
    use HasUlids;

    protected $fillable = [
        'name',
        'description',
        'definition',
        'team_id',
    ];

    protected function casts(): array
    {
        return [
            'id' => 'string',
            'definition' => PolicyDefinition::castAsCollection(),
        ];
    }
}
