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
        Schema::create('calls', function (Blueprint $table) {
            $table->id();
            $table->string('interaction_id')->unique();
            $table->string('call_type');
            $table->string('start_time');
            $table->string('end_time');
            $table->string('phone_number')->nullable();
            $table->integer('talk_time')->nullable();
            $table->integer('hold_time')->nullable();
            $table->integer('wait_time')->nullable();
            $table->string('recording')->nullable();
            $table->foreignId('disposition_id')->nullable()->constrained('dispositions');
            $table->boolean('agent_disconnected');
            $table->foreignId('agent_id')->nullable()->constrained('agents')->nullOnDelete();
            $table->foreignId('handling_agent_id')->nullable()->constrained('agents')->nullOnDelete();
            $table->foreignId('brand_id')->constrained('brands');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('calls');
    }
};
