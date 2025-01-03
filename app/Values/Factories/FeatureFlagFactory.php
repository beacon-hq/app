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
            'name' => $this->faker->word(),
            'slug' => $this->faker->word(),
            'description' => $this->faker->word(),
            'lastSeenAt' => $this->faker->word(),
            'featureType' => FeatureType::factory()->make(),
            'tags' => Tag::collect([Tag::factory()->make()]),
            'createdAt' => new Carbon(),
            'updatedAt' => new Carbon(),
        ];
    }
}
