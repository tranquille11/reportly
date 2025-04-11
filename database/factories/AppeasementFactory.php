<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Appeasement>
 */
class AppeasementFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'unique_id' => fake()->unique()->numberBetween(1, 100000),
            'order_id' => fake()->randomNumber(),
            'order_number' => 'BRAND#'.fake()->randomNumber(),
            'date' => fake()->dateTimeBetween('-3 months'),
            'note' => fake()->word(),
            'amount' => fake()->numberBetween(1000, 1000000),
            'reason_id' => fake()->numberBetween(1, 30),
            'brand_id' => fake()->numberBetween(1, 12),
        ];
    }
}
