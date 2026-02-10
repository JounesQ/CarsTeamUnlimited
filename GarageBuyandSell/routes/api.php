<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\Admin\VehicleController as AdminVehicleController;

// Public vehicle API (no auth)
Route::get('/vehicles', [VehicleController::class, 'index']);
Route::get('/vehicles/{id}', [VehicleController::class, 'show']);

// Admin vehicle API (no auth for now)
Route::prefix('admin')->group(function () {
    Route::get('/vehicles', [AdminVehicleController::class, 'index']);
    Route::post('/vehicles', [AdminVehicleController::class, 'store']);
    Route::post('/vehicles/upload-image', [AdminVehicleController::class, 'uploadImage']);
    Route::get('/vehicles/{id}', [AdminVehicleController::class, 'show']);
    Route::put('/vehicles/{id}', [AdminVehicleController::class, 'update']);
    Route::delete('/vehicles/{id}', [AdminVehicleController::class, 'destroy']);
});

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
