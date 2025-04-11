<?php

use Database\Seeders\ReportSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->string('type');
            $table->timestamps();
        });

        (new ReportSeeder)->run();
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
