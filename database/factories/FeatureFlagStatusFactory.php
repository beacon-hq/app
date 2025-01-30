<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Application;
use App\Models\Environment;
use App\Models\FeatureFlag;
use App\Models\FeatureFlagStatus;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class FeatureFlagStatusFactory extends Factory
{
    protected $model = FeatureFlagStatus::class;

    public function definition(): array
    {
        return [
            'status' => $this->faker->boolean(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
            'application_id' => Application::factory(),
            'environment_id' => Environment::factory(),
            'feature_flag_id' => FeatureFlag::factory(),
        ];
    }
}
