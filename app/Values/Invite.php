<?php

declare(strict_types=1);

namespace App\Values;

use App\Enums\Role;
use App\Models\Invite as InviteModel;
use App\Values\Collections\InviteCollection;
use Bag\Attributes\Collection;
use Bag\Attributes\Transforms;
use Bag\Bag;
use Illuminate\Notifications\RoutesNotifications;
use Illuminate\Support\Carbon;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[Collection(InviteCollection::class)]
#[TypeScript]
readonly class Invite extends Bag
{
    use RoutesNotifications;

    public function __construct(
        public string $email,
        public Role $role,
        public Team $team,
        public User $user,
        public Carbon $expires_at,
        public ?string $id = null,
    ) {
    }

    #[Transforms(InviteModel::class)]
    public static function fromModel(InviteModel $invite): array
    {
        return [
            'id' => $invite->id,
            'email' => $invite->email,
            'role' => $invite->role,
            'team' => Team::from($invite->team),
            'user' => User::from($invite->user),
            'expires_at' => $invite->expires_at,
        ];
    }
}
