<?php

declare(strict_types=1);

namespace App\Values\Factories;

use Bag\Factory;

class FeatureFlagContextFactory extends Factory
{
    public function definition(): array
    {
        return [
            'scopeType' => $this->faker->word(),
            'scope' => null,
            'appName' => $this->faker->word(),
            'environment' => $this->faker->word(),
            'sessionId' => $this->faker->uuid(),
            'ip' => $this->faker->word(),
            'userAgent' => $this->faker->word(),
            'referrer' => $this->faker->word(),
            'url' => $this->faker->word(),
            'method' => $this->faker->word(),
        ];
    }
}
