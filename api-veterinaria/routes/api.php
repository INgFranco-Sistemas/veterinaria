<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\VeterinarianController;
use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\PetController;

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
});