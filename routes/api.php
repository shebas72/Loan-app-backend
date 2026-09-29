<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\LoanApplicationController;
use App\Http\Controllers\Api\TenantController;
use App\Http\Controllers\Api\DocumentController;
use App\Http\Controllers\Api\StaffController;



Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/tenants', [TenantController::class, 'index']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::put('/me', [AuthController::class, 'updateProfile']);
    Route::put('/me/password', [AuthController::class, 'updatePassword']);
    Route::apiResource('loan-applications', LoanApplicationController::class);
    Route::post('/loan-applications/{loan_application}/transition', [LoanApplicationController::class, 'transition']);
    Route::patch('/loan-applications/{loan_application}/assign', [LoanApplicationController::class, 'assign']);
    Route::get('/loan-applications/{loan_application}/documents', [DocumentController::class, 'index']);
Route::post('/loan-applications/{loan_application}/documents', [DocumentController::class, 'store']);
Route::get('/tenants/mine', [TenantController::class, 'mine']);
Route::get('/staff', [StaffController::class, 'index']);
Route::post('/staff', [StaffController::class, 'store']);
Route::patch('/staff/{staff}', [StaffController::class, 'update']);
Route::patch('/staff/{staff}/status', [StaffController::class, 'updateStatus']);
Route::delete('/staff/{staff}', [StaffController::class, 'destroy']);
Route::get('/admin/tenants', [TenantController::class, 'adminIndex']);
Route::post('/admin/tenants', [TenantController::class, 'store']);
Route::get('/admin/tenants/{tenant}', [TenantController::class, 'adminShow']);
Route::patch('/admin/tenants/{tenant}', [TenantController::class, 'update']);
Route::patch('/admin/tenants/{tenant}/status', [TenantController::class, 'updateStatus']);
Route::delete('/admin/tenants/{tenant}', [TenantController::class, 'destroy']);
Route::post('/admin/tenants/{tenant}/users/{user}/reset-password', [TenantController::class, 'resetUserPassword']);
});

Route::get('/ping', function () {
    return response()->json(['message' => 'pong', 'timestamp' => now()]);
});