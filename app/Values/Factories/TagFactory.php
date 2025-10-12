<?php

declare(strict_types=1);

namespace App\Values\Factories;

use Bag\Factory;
use Carbon\CarbonImmutable;

class TagFactory extends Factory
{
    public function definition(): array
    {
        return [
            'id' => $this->faker->uuid(),
            'name' => $this->faker->word(),
            'description' => $this->faker->word(),
            'color' => $this->faker->hexColor(),
            'createdAt' => new CarbonImmutable(),
            'updatedAt' => new CarbonImmutable(),
        ];
    }
}
