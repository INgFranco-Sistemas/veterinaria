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
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $table->foreignId('pet_id')->constrained('pets')->cascadeOnDelete();
            $table->foreignId('veterinarian_id')->constrained('veterinarians')->cascadeOnDelete();

            $table->foreignId('slot_id')->constrained('availability_slots')->restrictOnDelete();

            $table->dateTime('starts_at');
            $table->dateTime('ends_at');

            $table->string('status', 20)->default('reserved'); // reserved | paid | attended | cancelled | no_show
            $table->text('reason')->nullable(); // motivo o detalle
            $table->text('notes')->nullable();  // observaciones internas

            $table->timestamps();

            $table->unique(['slot_id']); // un slot solo puede tener 1 cita
            $table->index(['veterinarian_id', 'starts_at']);
            $table->index(['client_id', 'starts_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
