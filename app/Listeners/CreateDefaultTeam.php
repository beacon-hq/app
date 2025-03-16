<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Enums\Color;
use App\Models\User;
use App\Repositories\UserRepository;
use App\Values\Team;
use Illuminate\Auth\Events\Registered;

class CreateDefaultTeam
{
    public function __construct(protected UserRepository $userRepository)
    {
    }

    /**
     * Handle the event.
     */
    public function handle(Registered $event): void
    {
        /** @var User $user */
        $user = $event->user;

        $team = Team::from(name: $user->first_name . '\'s Team', color: collect(Color::values())->random());

        $this->userRepository->addTeam(\App\Values\User::from($user), $team);
    }
}
