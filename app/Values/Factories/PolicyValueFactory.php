<?php

declare(strict_types=1);

namespace App\Values\Factories;

use App\Values\PolicyDefinition;
use Bag\Factory;

class PolicyValueFactory extends Factory
{
    public function definition(): array
    {
        return [
            'policyDefinition' => PolicyDefinition::factory()->make(),
            'value' => [],
            'status' => $this->faker->boolean(),
        ];
    }
}
