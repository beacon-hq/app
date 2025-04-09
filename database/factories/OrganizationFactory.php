<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrganizationFactory extends Factory
{
    /**
     * @inheritDoc
     */
    public function definition()
    {
        return [
            'owner_id' => User::factory()->create()->id,
            'name' => $this->faker->company(),
        ];
    }
}
