<?php

namespace Database\Seeders;

use App\Models\AppeasementReason;
use Illuminate\Database\Seeder;

class AppeasementReasonSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        AppeasementReason::insert(config('seed.appeasement-reasons'));
    }
}
