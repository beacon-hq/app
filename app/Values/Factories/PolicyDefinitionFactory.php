<?php

declare(strict_types=1);

namespace App\Values\Factories;

use App\Enums\PolicyDefinitionMatchOperator;
use App\Enums\PolicyDefinitionType;
use Bag\Factory;

class PolicyDefinitionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'type' => array_rand(PolicyDefinitionType::values()),
            'subject' => $this->faker->word(),
            'operator' => array_rand(PolicyDefinitionMatchOperator::values()),
        ];
    }
}
