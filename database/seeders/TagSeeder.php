<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Tags\Tag;

class TagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (config('seed.tags') as $type => $tags) {
            foreach ($tags as $tag) {
                Tag::findOrCreate($tag, $type);
            }
        }
    }
}
