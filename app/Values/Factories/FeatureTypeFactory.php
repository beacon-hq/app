<?php

declare(strict_types=1);

namespace App\Values\Factories;

use Bag\Factory;

class FeatureTypeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->word(),
            'id' => $this->faker->uuid(),
            'description' => $this->faker->word(),
            'temporary' => $this->faker->boolean(),
            'color' => $this->faker->hexColor(),
            'icon' => $this->faker->word(),
        ];
    }
}
