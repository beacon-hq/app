<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\PolicyDefinitionMatchOperator;
use App\Enums\PolicyDefinitionType;
use App\Models\Policy;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class PolicyFactory extends Factory
{
    protected $model = Policy::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'description' => $this->faker->text(),
            'definition' => [[
                'type' => $this->faker->randomElement(PolicyDefinitionType::values()),
                'subject' => $this->faker->word(),
                'operator' => $this->faker->randomElement(PolicyDefinitionMatchOperator::values()),
            ]],
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
