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
        Schema::create('availability_slots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('veterinarian_id')->constrained('veterinarians')->cascadeOnDelete();

            $table->string('service_type', 20); // 'appointment' | 'vaccine' | 'surgery'
            $table->dateTime('starts_at');
            $table->dateTime('ends_at');

            $table->string('status', 20)->default('available'); // available | blocked | booked
            $table->timestamps();

            $table->unique(['veterinarian_id', 'service_type', 'starts_at']); // evitar duplicados
            $table->index(['veterinarian_id', 'service_type', 'starts_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('availability_slots');
    }
};
