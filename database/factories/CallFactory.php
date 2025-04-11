<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Call>
 */
class CallFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'interaction_id' => fake()->unique()->iban(),
            'call_type' => 'inbound',
            'start_time' => fake()->dateTimeBetween('2024-08-01', '2024-08-31'),
            'end_time' => fake()->dateTimeBetween('2024-08-01', '2024-08-31'),
            'talk_time' => fake()->numberBetween(1, 1000),
            'agent_disconnected' => fake()->boolean(),
            'agent_id' => fake()->numberBetween(1, 100),
            'brand_id' => fake()->numberBetween(1, 10),
        ];
    }
}
