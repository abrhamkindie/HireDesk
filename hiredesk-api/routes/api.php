<?php

use App\Http\Controllers\AdminApplicationController;
use App\Http\Controllers\AdminVacancyController;
use App\Http\Controllers\CandidateApplicationController;
use App\Http\Controllers\CandidateAuthController;
use App\Http\Controllers\PublicVacancyController;
use App\Http\Controllers\StaffAuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/ping', function () {
    return response()->json([
        'app' => 'HireDesk',
        'message' => 'Hello from Laravel!',
        'time' => now()->toIso8601String(),
    ]);
});

Route::post('staff/login', [StaffAuthController::class, 'login']);

Route::post('candidate/register', [CandidateAuthController::class, 'register']);
Route::post('candidate/login', [CandidateAuthController::class, 'login']);

Route::get('vacancies', [PublicVacancyController::class, 'index']);
Route::get('vacancies/{vacancy:slug}', [PublicVacancyController::class, 'show']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', fn (Request $request) => $request->user());

    Route::post('candidate/logout', [CandidateAuthController::class, 'logout']);
    Route::get('candidate/applications', [CandidateApplicationController::class, 'index']);
    Route::post('vacancies/{vacancy:slug}/apply', [CandidateApplicationController::class, 'store']);
    Route::patch('candidate/applications/{application}/withdraw', [CandidateApplicationController::class, 'withdraw']);

    Route::prefix('admin')->group(function () {
        Route::apiResource('vacancies', AdminVacancyController::class);
        Route::get('applications', [AdminApplicationController::class, 'index']);
        Route::get('applications/{application}', [AdminApplicationController::class, 'show']);
        Route::patch('applications/{application}/status', [AdminApplicationController::class, 'updateStatus']);
    });
});
