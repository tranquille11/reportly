<?php

use Database\Seeders\AppeasementReasonSeeder;
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
        Schema::create('appeasement_reasons', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('shorthand')->unique();
            $table->boolean('has_percentage');
            $table->boolean('has_location');
            $table->boolean('has_product');
            $table->boolean('has_size');
            $table->softDeletes();
            $table->timestamps();
        });

        (new AppeasementReasonSeeder)->run();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appeasement_reasons');
    }
};
