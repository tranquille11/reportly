<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AppeasementReason>
 */
class AppeasementReasonFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {

        return [
            'name' => fake()->unique()->word(),
            'shorthand' => fake()->unique()->word(),
            'has_percentage' => Arr::random([true, false]),
            'has_location' => Arr::random([true, false]),
            'has_product' => Arr::random([true, false]),
            'has_size' => Arr::random([true, false]),
        ];
    }
}
