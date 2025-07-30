<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\FeatureFlag;
use App\Models\FeatureType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class FeatureFlagFactory extends Factory
{
    protected $model = FeatureFlag::class;

    public function definition(): array
    {
        return [
            'name' => Str::slug($this->faker->words(3, true)),
            'description' => $this->faker->sentence(),
            'last_seen_at' => Carbon::now()->subDays($this->faker->numberBetween(1, 7)),
            'feature_type_id' => FeatureType::factory(),
            'team_id' => null,
            'created_at' => Carbon::now()->subDays($this->faker->numberBetween(1, 365)),
            'updated_at' => Carbon::now()->subDays($this->faker->numberBetween(1, 7)),
            'status' => false,
        ];
    }

    public function active(): static
    {
        return $this->state(['status' => true]);
    }
}
