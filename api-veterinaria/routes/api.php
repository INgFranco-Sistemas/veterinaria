<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\VeterinarianController;
use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\PetController;
use App\Http\Controllers\Api\VetScheduleController;
use App\Http\Controllers\Api\AvailabilitySlotController;
use App\Http\Controllers\Api\AppointmentController;

Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
});

Route::middleware(['auth:api', 'permission:kpis.view'])->get('/kpis/test', function () {
    return response()->json(['ok' => true, 'message' => 'Tienes permiso para ver KPIs']);
});

Route::middleware(['auth:api'])->group(function () {
    Route::apiResource('veterinarians', VeterinarianController::class);
    Route::apiResource('clients', ClientController::class);
    Route::apiResource('pets', PetController::class);

    // Horarios por veterinario
    Route::get('veterinarians/{veterinarian}/schedules', [VetScheduleController::class, 'index']);
    Route::post('veterinarians/{veterinarian}/schedules', [VetScheduleController::class, 'store']);
    Route::get('veterinarians/{veterinarian}/schedules/{schedule}', [VetScheduleController::class, 'show']);
    Route::put('veterinarians/{veterinarian}/schedules/{schedule}', [VetScheduleController::class, 'update']);
    Route::delete('veterinarians/{veterinarian}/schedules/{schedule}', [VetScheduleController::class, 'destroy']);

    // Slots
    Route::get('slots', [AvailabilitySlotController::class, 'index']);
    Route::post('veterinarians/{veterinarian}/slots/generate', [AvailabilitySlotController::class, 'generate']);
    Route::delete('veterinarians/{veterinarian}/slots/delete-range', [AvailabilitySlotController::class, 'deleteRange']);

    Route::apiResource('appointments', AppointmentController::class);
    Route::post('appointments/{appointment}/cancel', [AppointmentController::class, 'cancel']);
});