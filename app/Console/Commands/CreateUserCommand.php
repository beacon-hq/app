<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\Color;
use App\Enums\Role;
use App\Models\Team;
use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use function Laravel\Prompts\outro;
use function Laravel\Prompts\password;
use function Laravel\Prompts\select;
use function Laravel\Prompts\text;
use Str;

class CreateUserCommand extends Command
{
    protected $signature = 'beacon:create-user {--role=}';

    protected $description = 'Create a new User';

    public function handle(UserRepository $userRepository): void
    {
        DB::transaction(function () use ($userRepository) {
            $firstName = text('First Name', required: true);
            $lastName = text('Last Name', required: true);
            $email = text('Email', required: true);
            $password = password('Password', required: true);

            $role = $this->option('role') ?? select('Role', Role::values(), default: Role::DEVELOPER->value);

            $user = User::createQuietly([
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => $email,
                'password' => bcrypt($password),
            ]);

            $user->assignRole($role);

            $teams = collect(['beacon_create_team' => 'Create a new team…'])->merge(Team::all()->mapWithKeys(function (Team $team) {
                if (!$owner = $team->owner) {
                    return [];
                }

                return [$team->id => Str::of($team->name)->append(' (Owner: ')->append($owner->email)->append(')')->__toString()];
            }));

            $team = select('Team', $teams);

            if ($team === 'beacon_create_team') {
                $team = Team::create(['name' => text('Team Name', default: $firstName . '\'s Team', required: true), 'color' => collect(Color::values())->random()])->id;
            }

            $userRepository->addTeam(\App\Values\User::from($user), \App\Values\Team::from(id: $team));

            outro('User created successfully');
        });
    }
}
