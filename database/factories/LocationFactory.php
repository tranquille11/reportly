<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Location>
 */
class LocationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $number = fake()->unique()->numberBetween(1, 145);
        $type = Arr::random(['store', 'warehouse']);

        return [
            'name' => "$type $number",
            'number' => $number,
            'type' => $type,
            'parent_id' => null,
        ];
    }
}
