<?php

namespace Database\Seeders;

use App\Models\Disposition;
use Illuminate\Database\Seeder;

class DispositionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Disposition::insert(config('seed.dispositions'));
    }
}
