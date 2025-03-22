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
use Illuminate\Support\Facades\Gate;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

/**
 * @method static static from(?string|TeamModel $id = null, ?string $name = null, ?string $color = null, ?string $icon = null)
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
        public ?Organization $organization,
        #[FromRouteParameter]
        public ?string $slug,
        #[FromRouteParameter]
        public ?string $id = null,
        public ?string $name = null,
        public string|Color|null $color = null,
        public ?string $icon = null,
        #[Cast(Collection::class, User::class)]
        public ?UserCollection $members = null,
    ) {
    }

    #[Transforms(TeamModel::class)]
    public static function fromModel(TeamModel $team): array
    {
        return [
            'organization' => $team->organization,
            'slug' => $team->slug,
            'id' => $team->id,
            'name' => $team->name,
            'color' => $team->color,
            'icon' => $team->icon,
            'members' => Gate::check('update', $team) && $team->relationLoaded('users') ? User::collect($team->users) : null,
        ];
    }

    public static function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'color' => ['required', 'string', 'max:255'],
            'icon' => ['nullable', 'string', 'max:255'],
        ];
    }
}
