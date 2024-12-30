<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Application;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class ApplicationFactory extends Factory
{
    protected $model = Application::class;

    public function definition(): array
    {
        $name = $this->faker->domainWord() . '-' . $this->faker->domainWord();

        return [
            'name' => $name,
            'display_name' => $name,
            'description' => $this->faker->text(),
            'color' => $this->faker->hexColor(),
            'last_seen_at' => Carbon::now(),
            'tenant_id' => Tenant::factory(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
