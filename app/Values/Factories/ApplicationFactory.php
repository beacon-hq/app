<?php

declare(strict_types=1);

namespace App\Values\Factories;

use App\Values\Environment;
use Bag\Factory;

class ApplicationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'id' => $this->faker->uuid(),
            'name' => $this->faker->word(),
            'display_name' => $this->faker->word(),
            'description' => $this->faker->word(),
            'last_seen_at' => $this->faker->word(),
            'color' => $this->faker->hexColor(),
            'environments' => Environment::collect([Environment::factory()]),
        ];
    }
}
