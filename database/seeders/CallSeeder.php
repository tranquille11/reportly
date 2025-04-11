<?php

namespace Database\Seeders;

use App\Models\Agent;
use App\Models\Brand;
use App\Models\Call;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CallSeeder extends Seeder
{
    public function run(): void
    {
        $agents = Agent::all();
        $brands = Brand::all();

        foreach ($agents as $agent) {
            // Create 100 calls for each agent
            $calls = collect(range(1, 100))->map(function () use ($agent, $agents, $brands) {
                $startTime = fake()->dateTimeBetween('2024-12-01', '2024-12-31');
                $talkTime = fake()->numberBetween(60, 3600); // Between 1 minute and 1 hour
                $holdTime = fake()->boolean(30) ? fake()->numberBetween(10, 300) : 0; // 30% chance of hold
                $waitTime = fake()->numberBetween(5, 120); // Wait time between 5s and 2min

                return [
                    'interaction_id' => Str::uuid(),
                    'call_type' => fake()->randomElement(['inbound', 'outbound']),
                    'start_time' => $startTime->format('Y-m-d H:i:s'),
                    'end_time' => (clone $startTime)->modify("+{$talkTime} seconds")->format('Y-m-d H:i:s'),
                    'phone_number' => fake()->e164PhoneNumber(),
                    'talk_time' => $talkTime,
                    'hold_time' => $holdTime,
                    'wait_time' => $waitTime,
                    'recording' => fake()->boolean(80) ? 'https://recordings.example.com/'.Str::uuid().'.mp3' : null,
                    'disposition_id' => null, // You might want to create dispositions first
                    'agent_disconnected' => fake()->boolean(10), // 10% chance of disconnect
                    'agent_id' => $agent->id,
                    'handling_agent_id' => fake()->boolean(20) ? $agents->random()->id : null, // 20% chance of transfer
                    'brand_id' => $brands->random()->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            });

            // Insert in chunks to avoid memory issues
            foreach ($calls->chunk(25) as $chunk) {
                Call::insert($chunk->toArray());
            }
        }
    }
}
