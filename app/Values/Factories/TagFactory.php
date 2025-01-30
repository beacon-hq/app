<?php

declare(strict_types=1);

namespace App\Values\Factories;

use Bag\Factory;
use Illuminate\Support\Carbon;

class TagFactory extends Factory
{
    public function definition(): array
    {
        return [
            'id' => $this->faker->uuid(),
            'slug' => $this->faker->word(),
            'name' => $this->faker->word(),
            'description' => $this->faker->word(),
            'color' => $this->faker->hexColor(),
            'createdAt' => new Carbon(),
            'updatedAt' => new Carbon(),
        ];
    }
}
