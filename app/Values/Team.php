<?php

declare(strict_types=1);

namespace App\Values;

use App\Enums\Color;
use App\Models\Team as TeamModel;
use App\Values\Collections\TeamCollection;
use App\Values\Collections\UserCollection;
use App\Values\Factories\TeamFactory;
use Bag\Attributes\Cast;
use Bag\Attributes\Collection;
use Bag\Attributes\Factory;
use Bag\Attributes\Laravel\FromRouteParameter;
use Bag\Attributes\MapName;
use Bag\Attributes\StripExtraParameters;
use Bag\Attributes\Transforms;
use Bag\Bag;
use Bag\Mappers\SnakeCase;
use Bag\Traits\HasFactory;
use Bag\Validation\Rules\OptionalOr;
use Bag\Values\Optional;
use Illuminate\Support\Facades\Gate;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;
use TypeError;

/**
 * @method static static from(Optional|string $id, Optional|Organization $organization, Optional|string $name, Optional|string $icon, Optional|UserCollection $members, Color|string|null $color = null)
 * @method static TeamCollection<Team> collect(iterable $items)
 * @method static TeamFactory<Team> factory(Collection|array|int $data = [])
 */
#[Collection(TeamCollection::class)]
#[Factory(TeamFactory::class)]
#[MapName(SnakeCase::class, SnakeCase::class)]
#[StripExtraParameters]
#[TypeScript]
readonly class Team extends Bag
{
    use HasFactory;

    public function __construct(
        #[FromRouteParameter('team')]
        public Optional|string $id,
        public Optional|Organization $organization,
        public Optional|string $name,
        public Optional|string $icon,
        #[Cast(Collection::class, User::class)]
        public Optional|UserCollection $members,
        public Color|string|null $color = null,
    ) {
    }

    #[Transforms(TeamModel::class)]
    public static function fromModel(TeamModel $team): array
    {
        try {
            return [
                'id' => $team->id,
                'organization' => $team->organization,
                'name' => $team->name,
                'color' => $team->color,
                'icon' => $team->icon,
                'members' => Gate::check('update', $team) && $team->relationLoaded('users') ? User::collect($team->users) : null,
            ];
        } catch (TypeError) {
            return [
                'id' => $team->id,
                'organization' => $team->organization,
                'name' => $team->name,
                'color' => $team->color,
                'icon' => $team->icon,
                'members' => null,
            ];
        }
    }

    public static function rules(): array
    {
        return [
            'name' => [new OptionalOr(['required', 'string', 'max:255'])],
            'color' => [new OptionalOr(['nullable', 'string', 'max:255'])],
            'icon' => [new OptionalOr(['nullable', 'string', 'max:255'])],
        ];
    }
}
