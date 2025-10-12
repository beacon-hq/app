<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\Role;
use App\Models\Invite;
use App\Models\Organization;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;

class InviteFactory extends Factory
{
    protected $model = Invite::class;

    public function definition(): array
    {
        return [
            'email' => $this->faker->unique()->safeEmail(),
            'role' => Arr::random(Role::cases()),
            'team_id' => Team::factory(),
            'organization_id' => Organization::factory(),
            'user_id' => User::factory(),
            'expires_at' => now(),
        ];
    }
}
