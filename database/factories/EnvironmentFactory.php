<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Environment;
use App\Models\Team;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class EnvironmentFactory extends Factory
{
    protected $model = Environment::class;

    public function definition(): array
    {
        $name = $this->faker->domainWord() . '-' . $this->faker->domainWord();

        return [
            'name' => $name,
            'description' => $this->faker->text(),
            'color' => $this->faker->hexColor(),
            'team_id' => Team::factory(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
