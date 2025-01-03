<?php

declare(strict_types=1);

namespace App\Values\Factories;

use App\Values\PolicyDefinition;
use Bag\Factory;
use Illuminate\Support\Carbon;

class PolicyFactory extends Factory
{
    public function definition(): array
    {
        return [
            'slug' => $this->faker->word(),
            'name' => $this->faker->word(),
            'description' => $this->faker->word(),
            'id' => $this->faker->uuid(),
            'definition' => PolicyDefinition::collect([PolicyDefinition::factory()->make()]),
            'createdAt' => new Carbon(),
            'updatedAt' => new Carbon(),
        ];
    }
}
