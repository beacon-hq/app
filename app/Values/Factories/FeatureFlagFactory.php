<?php

declare(strict_types=1);

namespace App\Values\Factories;

use App\Values\FeatureType;
use App\Values\Tag;
use Bag\Factory;
use Illuminate\Support\Carbon;

class FeatureFlagFactory extends Factory
{
    public function definition(): array
    {
        return [
            'id' => $this->faker->uuid(),
            'name' => $this->faker->word(),
            'description' => $this->faker->word(),
            'lastSeenAt' => $this->faker->word(),
            'featureType' => FeatureType::factory()->make(),
            'tags' => Tag::collect([Tag::factory()->make()]),
            'createdAt' => new Carbon(),
            'updatedAt' => new Carbon(),
        ];
    }
}
