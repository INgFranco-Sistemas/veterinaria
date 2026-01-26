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
        Schema::create('vet_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('veterinarian_id')->constrained('veterinarians')->cascadeOnDelete();

            $table->unsignedTinyInteger('weekday'); // 1=Lunes ... 7=Domingo
            $table->time('start_time');            // 09:00
            $table->time('end_time');              // 13:00
            $table->unsignedSmallInteger('slot_minutes')->default(30); // 15/20/30/60
            $table->boolean('active')->default(true);

            $table->timestamps();

            $table->unique(['veterinarian_id', 'weekday']); // 1 horario por día
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vet_schedules');
    }
};
