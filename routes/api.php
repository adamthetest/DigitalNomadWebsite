<?php

use App\Http\Controllers\Api\V1\CityController;
use App\Http\Controllers\Api\V1\JobController;
use App\Http\Controllers\Api\V1\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// API v1 routes
Route::prefix('v1')->group(function () {

    // Cities API
    Route::prefix('cities')->group(function () {
        Route::get('/', [CityController::class, 'index']);
        Route::get('/recommendations', [CityController::class, 'recommendations']);
        Route::get('/{city}', [CityController::class, 'show']);
        Route::get('/{city}/ai-context', [CityController::class, 'aiContext']);
    });

    // Jobs API
    Route::prefix('jobs')->group(function () {
        Route::get('/', [JobController::class, 'index']);
        Route::get('/recommendations', [JobController::class, 'recommendations']);
        Route::get('/statistics', [JobController::class, 'statistics']);
        Route::get('/{job}', [JobController::class, 'show']);
        Route::get('/{job}/ai-context', [JobController::class, 'aiContext']);
    });

    // Users API
    Route::prefix('users')->group(function () {
        Route::get('/', [UserController::class, 'index']);
        Route::get('/recommendations', [UserController::class, 'recommendations']);
        Route::get('/statistics', [UserController::class, 'statistics']);
        Route::get('/{user}', [UserController::class, 'show']);
        Route::get('/{user}/ai-context', [UserController::class, 'aiContext']);
    });
});

// Health check endpoint
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now()->toISOString(),
        'version' => '1.0.0',
    ]);
});
