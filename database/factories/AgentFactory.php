<?php

namespace Database\Factories;

use App\Enums\AgentRole;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Agent>
 */
class AgentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'stage_name' => fake()->firstName(),
            'email' => fake()->unique()->email(),
            'role' => fake()->randomElement(AgentRole::cases())->value,
            'settings' => ['gorgias_user_id' => fake()->numberBetween(100000, 9999999)],
        ];
    }
}
