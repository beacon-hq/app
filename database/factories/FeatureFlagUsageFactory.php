<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Application;
use App\Models\Environment;
use App\Models\FeatureFlag;
use App\Models\FeatureFlagUsage;
use App\Models\Team;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class FeatureFlagUsageFactory extends Factory
{
    protected $model = FeatureFlagUsage::class;

    public function definition(): array
    {
        return [
            'evaluated_at' => Carbon::now(),
            'value' => null,
            'active' => $this->faker->boolean(),
            'feature_flag_name' => $this->faker->domainWord(),
            'application_name' => $this->faker->domainWord(),
            'environment_name' => $this->faker->domainWord(),

            'environment_id' => Environment::factory(),
            'application_id' => Application::factory(),
            'feature_flag_id' => FeatureFlag::factory(),
            'team_id' => Team::factory(),
        ];
    }
}
