<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\LoanApplicationController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::apiResource('loan-applications', LoanApplicationController::class);
    Route::post('/loan-applications/{loanApplication}/transition', [LoanApplicationController::class, 'transition']);
});

Route::get('/ping', function () {
    return response()->json(['message' => 'pong', 'timestamp' => now()]);
});