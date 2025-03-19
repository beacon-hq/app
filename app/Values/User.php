<?php

declare(strict_types=1);

namespace App\Values;

use App\Enums\Role;
use App\Models\User as UserModel;
use App\Values\Collections\TeamCollection;
use App\Values\Collections\UserCollection;
use Bag\Attributes\Cast;
use Bag\Attributes\Collection;
use Bag\Attributes\MapName;
use Bag\Attributes\StripExtraParameters;
use Bag\Attributes\Transforms;
use Bag\Bag;
use Bag\Mappers\SnakeCase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection as LaravelCollection;
use Spatie\TypeScriptTransformer\Attributes\LiteralTypeScriptType;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[Collection(UserCollection::class)]
#[MapName(SnakeCase::class, SnakeCase::class)]
#[StripExtraParameters]
#[TypeScript]
readonly class User extends Bag
{
    public function __construct(
        public ?int $id = null,
        public ?Team $team = null,
        public ?string $name = null,
        public ?string $firstName = null,
        public ?string $lastName = null,
        public ?string $email = null,
        public ?string $avatar = null,
        public ?string $gravatar = null,
        public string $theme = 'system',
        #[Cast(Collection::class, Team::class)]
        public ?TeamCollection $teams = null,
        #[LiteralTypeScriptType('Role[]')]
        public ?LaravelCollection $roles = null,
        public ?Carbon $emailVerifiedAt = null,
    ) {
    }

    #[Transforms(UserModel::class)]
    public static function fromModel(UserModel $user): array
    {
        return [
            'id' => $user->id,
            'team' => $user->team,
            'name' => $user->name,
            'firstName' => $user->first_name,
            'lastName' => $user->last_name,
            'email' => $user->email,
            'avatar' => $user->avatar,
            'gravatar' => $user->gravatar,
            'theme' => $user->theme,
            'teams' => $user->relationLoaded('teams') ? $user->teams : null,
            'roles' => collect($user->roles->pluck('name')->map(fn (string $role) => Role::tryFrom($role)?->value)),
            'emailVerifiedAt' => $user->email_verified_at,
        ];
    }
}
