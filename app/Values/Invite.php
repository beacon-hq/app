<?php

declare(strict_types=1);

namespace App\Values;

use App\Enums\Role;
use App\Models\Invite as InviteModel;
use App\Values\Collections\InviteCollection;
use Bag\Attributes\Collection;
use Bag\Attributes\Computed;
use Bag\Attributes\Laravel\FromRouteParameter;
use Bag\Attributes\MapName;
use Bag\Attributes\Transforms;
use Bag\Bag;
use Bag\Mappers\SnakeCase;
use Bag\Values\Optional;
use Carbon\CarbonImmutable;
use Creativeorange\Gravatar\Facades\Gravatar;
use Illuminate\Notifications\RoutesNotifications;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Spatie\TypeScriptTransformer\Attributes\Optional as TypeScriptOptional;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

/**
 * @method static static from(Optional|string $id, string $email, Role $role, Team $team, User $user, Carbon $expires_at)
 * @method static InviteCollection<Invite> collect(iterable $items)
 */
#[Collection(InviteCollection::class)]
#[MapName(SnakeCase::class, SnakeCase::class)]
#[TypeScript]
readonly class Invite extends Bag
{
    use RoutesNotifications;

    #[Computed]
    #[TypeScriptOptional]
    public ?string $avatar;

    public function __construct(
        #[FromRouteParameter('invite')]
        public Optional|string $id,
        public Optional|string $email,
        public Optional|Role $role,
        public Optional|Team $team,
        public Optional|Organization $organization,
        public Optional|User $user,
        public CarbonImmutable|Optional $expires_at,
    ) {
        $this->avatar = $this->has('email') ? Gravatar::get($this->email) : null;
    }

    #[Transforms(InviteModel::class)]
    public static function fromModel(InviteModel $invite): array
    {
        return [
            'id' => $invite->id,
            'email' => $invite->email,
            'role' => $invite->role,
            'team' => Team::from($invite->team),
            'organization' => Organization::from($invite->organization),
            'user' => User::from($invite->user),
            'expires_at' => $invite->expires_at,
        ];
    }

    public static function rules(): array
    {
        return [
            'email' => [
                'required',
                'email',
            ],
            'role' => ['required', Rule::enum(Role::class)],
            'team' => ['required'],
            'organization' => ['required'],
            'user' => ['required'],
            'expires_at' => ['required', 'date'],
        ];
    }
}
