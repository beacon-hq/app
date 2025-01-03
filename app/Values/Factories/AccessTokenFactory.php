<?php

declare(strict_types=1);

namespace App\Values\Factories;

use Bag\Factory;

class AccessTokenFactory extends Factory
{
    public function definition(): array
    {
        return [
            'id' => $this->faker->randomNumber(),
            'name' => $this->faker->word(),
            'token' => $this->faker->word(),
            'last_used_at' => $this->faker->word(),
            'created_at' => $this->faker->word(),
        ];
    }
}
