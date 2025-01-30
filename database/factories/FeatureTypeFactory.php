<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\FeatureType;
use App\Models\Team;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class FeatureTypeFactory extends Factory
{
    protected $model = FeatureType::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'description' => $this->faker->text(),
            'temporary' => $this->faker->boolean(),
            'color' => $this->faker->hexColor(),
            'icon' => $this->faker->word(),
            'team_id' => Team::factory(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
