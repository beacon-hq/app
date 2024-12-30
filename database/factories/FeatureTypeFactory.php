<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\FeatureType;
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
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
