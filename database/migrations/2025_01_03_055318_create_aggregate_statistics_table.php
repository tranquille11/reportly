<?php

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
        Schema::create('aggregate_statistics', function (Blueprint $table) {
            $table->id();
            $table->string('statistic');
            $table->string('number');
            $table->json('data')->nullable();
            $table->date('start_date');
            $table->date('end_date');
            $table->foreignId('agent_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aggregate_data');
    }
};
