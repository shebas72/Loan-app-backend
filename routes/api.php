<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\LoanApplicationController;
use App\Http\Controllers\Api\TenantController;
use App\Http\Controllers\Api\DocumentController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::apiResource('loan-applications', LoanApplicationController::class);
    Route::post('/loan-applications/{loan_application}/transition', [LoanApplicationController::class, 'transition']);
    Route::get('/tenants', [TenantController::class, 'index']);
    Route::get('/loan-applications/{loan_application}/documents', [DocumentController::class, 'index']);
Route::post('/loan-applications/{loan_application}/documents', [DocumentController::class, 'store']);
});

Route::get('/ping', function () {
    return response()->json(['message' => 'pong', 'timestamp' => now()]);
});