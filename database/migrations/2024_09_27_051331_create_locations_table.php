<?php

use Database\Seeders\LocationSeeder;
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
        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            $table->integer('number')->unique();
            $table->string('name')->unique();
            $table->string('type');
            $table->foreignId('parent_id')->nullable()->constrained('locations')->nullOnDelete();
            $table->timestamps();
        });

        (new LocationSeeder)->run();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('locations');
    }
};
