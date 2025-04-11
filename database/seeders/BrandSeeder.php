<?php

namespace Database\Seeders;

use App\Models\Brand;
use Illuminate\Database\Seeder;

class BrandSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (config('seed.brands') as $brand) {
            $newBrand = Brand::firstOrCreate(
                ['name' => $brand['name']],
                ['shorthand' => $brand['shorthand']]
            );
            $newBrand->syncTagsWithType($brand['tags'], 'talkdesk');
        }
    }
}
