<?php

declare(strict_types=1);

namespace App\Values\Factories;

use Bag\Factory;

class EnvironmentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->word(),
            'description' => $this->faker->word(),
            'slug' => $this->faker->word(),
            'color' => $this->faker->hexColor(),
        ];
    }
}
