<?php

declare(strict_types=1);

namespace App\Values;

use App\Enums\Role;
use App\Enums\UserStatus;
use App\Models\User as UserModel;
use App\Values\Collections\TeamCollection;
use App\Values\Collections\UserCollection;
use Bag\Attributes\Cast;
use Bag\Attributes\Collection;
use Bag\Attributes\HiddenFromJson;
use Bag\Attributes\Laravel\FromRouteParameter;
use Bag\Attributes\MapName;
use Bag\Attributes\StripExtraParameters;
use Bag\Attributes\Transforms;
use Bag\Bag;
use Bag\Mappers\SnakeCase;
use Bag\Values\Optional;
use Carbon\CarbonImmutable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection as LaravelCollection;
use Illuminate\Support\Facades\Auth;
use Spatie\TypeScriptTransformer\Attributes\Hidden as HiddenFromTypeScript;
use Spatie\TypeScriptTransformer\Attributes\LiteralTypeScriptType;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

/**
 * @method static static from(Optional|int $id, Optional|Team $team, Optional|string $name, Optional|string $firstName, Optional|string $lastName, Optional|string $email, Optional|string $password, Optional|string $avatar, Optional|string $gravatar, Optional|TeamCollection $teams, Collection|Optional $roles, Optional|UserStatus $status, string $theme = 'system', Carbon|null $emailVerifiedAt = null)
 * @method static UserCollection<User> collect(iterable $items)
 */
#[Collection(UserCollection::class)]
#[MapName(SnakeCase::class, SnakeCase::class)]
#[StripExtraParameters]
#[TypeScript]
readonly class User extends Bag
{
    public function __construct(
        #[FromRouteParameter('user')]
        public Optional|int $id,
        public Optional|Team $team,
        public Optional|string $name,
        public Optional|string $firstName,
        public Optional|string $lastName,
        public Optional|string $email,
        #[HiddenFromJson]
        #[HiddenFromTypeScript]
        public Optional|string $password,
        public Optional|string $avatar,
        public Optional|string $gravatar,
        #[Cast(Collection::class, Team::class)]
        public Optional|TeamCollection $teams,
        #[LiteralTypeScriptType('Role[]')]
        public LaravelCollection|Optional $roles,
        public Optional|UserStatus $status,
        public string $theme = 'system',
        public ?CarbonImmutable $emailVerifiedAt = null,
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
            'theme' => $user->theme ?? 'system',
            'teams' => $user->relationLoaded('teams') ? $user->teams : null,
            'roles' => Auth::user() instanceof UserModel ? collect($user->roles->pluck('name')->map(fn (string $role) => Role::tryFrom($role)?->value)) : null,
            'emailVerifiedAt' => $user->email_verified_at,
            'status' => $user->status,
        ];
    }
}
