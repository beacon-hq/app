<?php

declare(strict_types=1);

namespace App\Values\Factories;

use App\Values\FeatureType;
use App\Values\Tag;
use Bag\Factory;
use Carbon\CarbonImmutable;

class FeatureFlagFactory extends Factory
{
    public function definition(): array
    {
        return [
            'id' => $this->faker->uuid(),
            'name' => $this->faker->word(),
            'description' => $this->faker->word(),
            'lastSeenAt' => new CarbonImmutable(),
            'featureType' => FeatureType::factory()->make(),
            'tags' => Tag::collect([Tag::factory()->make()]),
            'createdAt' => new CarbonImmutable(),
            'updatedAt' => new CarbonImmutable(),
        ];
    }
}
