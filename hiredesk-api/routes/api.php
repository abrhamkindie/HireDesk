<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/ping', function () {
    return response()->json([
        'app' => 'HireDesk',
        'message' => 'Hello from Laravel!',
        'time' => now()->toIso8601String(),
    ]);
});

// HR-only routes
Route::middleware(['auth:web', 'role:admin|hr'])->prefix('admin')->group(function () {
    Route::apiResource('vacancies', AdminVacancyController::class);
    Route::get('applications', [AdminApplicationController::class, 'index']);
    Route::patch('applications/{id}/status', [AdminApplicationController::class, 'updateStatus']);
});