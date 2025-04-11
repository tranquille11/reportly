<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->jsonb('value');
            $table->timestamps();
        });

        Setting::create([
            'key' => 'holidays',
            'value' => [
                'easter' => [
                    'name' => 'Easter',
                    'date' => '2025-12-31',
                ],
                'christmas' => [
                    'name' => 'Christmas',
                    'date' => '2025-12-25',
                ],
                'new_years' => [
                    'name' => 'New Years',
                    'date' => '2025-01-01',
                ],
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
