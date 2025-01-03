<?php

declare(strict_types=1);

namespace App\Values\Factories;

use App\Values\FeatureType;
use Bag\Factory;

class FeatureFlagFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->word(),
            'id' => $this->faker->uuid(),
            'description' => $this->faker->word(),
            'lastSeenAt' => $this->faker->word(),
            'featureType' => FeatureType::factory()->make(),
        ];
    }
}
