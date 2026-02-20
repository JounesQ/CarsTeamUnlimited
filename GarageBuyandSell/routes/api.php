<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\Admin\VehicleController as AdminVehicleController;
use App\Http\Controllers\Admin\AuthController as AdminAuthController;

// Public vehicle API (no auth)
Route::get('/vehicles', [VehicleController::class, 'index']);
Route::get('/vehicles/{id}', [VehicleController::class, 'show']);

// Admin auth (no auth required for login)
Route::prefix('admin')->group(function () {
    Route::post('/login', [AdminAuthController::class, 'login']);
    Route::post('/logout', [AdminAuthController::class, 'logout'])->middleware('auth:sanctum');
    Route::get('/me', [AdminAuthController::class, 'me'])->middleware('auth:sanctum');
});

// Admin vehicle API (requires auth + admin)
Route::prefix('admin')->middleware(['auth:sanctum', 'admin'])->group(function () {
    Route::get('/vehicles', [AdminVehicleController::class, 'index']);
    Route::get('/vehicles/stats', [AdminVehicleController::class, 'stats']);
    Route::post('/vehicles', [AdminVehicleController::class, 'store']);
    Route::post('/vehicles/upload-image', [AdminVehicleController::class, 'uploadImage']);
    Route::get('/vehicles/{id}', [AdminVehicleController::class, 'show']);
    Route::put('/vehicles/{id}', [AdminVehicleController::class, 'update']);
    Route::delete('/vehicles/{id}', [AdminVehicleController::class, 'destroy']);
});

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
