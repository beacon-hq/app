<?php

declare(strict_types=1);

namespace App\Values\Factories;

use App\Values\Application;
use App\Values\Environment;
use App\Values\FeatureFlag;
use App\Values\Policy;
use Bag\Factory;

class FeatureFlagStatusFactory extends Factory
{
    public function definition(): array
    {
        return [
            'application' => Application::factory()->make(),
            'environment' => Environment::factory()->make(),
            'featureFlag' => FeatureFlag::factory()->make(),
            'policies' => Policy::collect([Policy::factory()->make()]),
            'status' => $this->faker->boolean(),
            'id' => $this->faker->uuid(),
        ];
    }
}
