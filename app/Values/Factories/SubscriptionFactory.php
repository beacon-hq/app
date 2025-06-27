<?php

declare(strict_types=1);

namespace App\Values\Factories;

use App\Values\Product;
use Bag\Factory;

class SubscriptionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'id' => $this->faker->uuid(),
            'stripeId' => $this->faker->uuid(),
            'status' => $this->faker->word(),
            'plan' => Product::factory()->make(),
            'trialEndsAt' => $this->faker->word(),
            'endsAt' => $this->faker->word(),
            'createdAt' => $this->faker->word(),
        ];
    }
}
