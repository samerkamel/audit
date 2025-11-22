<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ExternalAuditController;
use App\Http\Controllers\Api\CarController;
use App\Http\Controllers\Api\CertificateController;
use App\Http\Controllers\Api\DocumentController;
use App\Http\Controllers\Api\ComplaintController;

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

// API Version 1
Route::prefix('v1')->group(function () {

    // Public routes (no authentication required)
    Route::post('/auth/login', [AuthController::class, 'login'])
        ->middleware('throttle:auth');

    // Protected routes (authentication required)
    Route::middleware('auth:sanctum')->group(function () {

        // Authentication routes
        Route::prefix('auth')->group(function () {
            Route::post('/logout', [AuthController::class, 'logout']);
            Route::post('/logout-all', [AuthController::class, 'logoutAll']);
            Route::get('/profile', [AuthController::class, 'profile']);
            Route::get('/tokens', [AuthController::class, 'tokens']);
            Route::delete('/tokens/{tokenId}', [AuthController::class, 'revokeToken']);
        });

        // External Audits API
        Route::prefix('audits')->group(function () {
            Route::get('/', [ExternalAuditController::class, 'index']);
            Route::post('/', [ExternalAuditController::class, 'store']);
            Route::get('/statistics', [ExternalAuditController::class, 'statistics']);
            Route::get('/{id}', [ExternalAuditController::class, 'show']);
            Route::put('/{id}', [ExternalAuditController::class, 'update']);
            Route::patch('/{id}', [ExternalAuditController::class, 'update']);
            Route::delete('/{id}', [ExternalAuditController::class, 'destroy']);
        });

        // CARs (Corrective Action Requests) API
        Route::prefix('cars')->group(function () {
            Route::get('/', [CarController::class, 'index']);
            Route::post('/', [CarController::class, 'store']);
            Route::get('/statistics', [CarController::class, 'statistics']);
            Route::get('/{id}', [CarController::class, 'show']);
            Route::put('/{id}', [CarController::class, 'update']);
            Route::patch('/{id}', [CarController::class, 'update']);
            Route::delete('/{id}', [CarController::class, 'destroy']);
        });

        // Certificates API
        Route::prefix('certificates')->group(function () {
            Route::get('/', [CertificateController::class, 'index']);
            Route::post('/', [CertificateController::class, 'store']);
            Route::get('/statistics', [CertificateController::class, 'statistics']);
            Route::get('/expiring', [CertificateController::class, 'expiring']);
            Route::get('/{id}', [CertificateController::class, 'show']);
            Route::put('/{id}', [CertificateController::class, 'update']);
            Route::patch('/{id}', [CertificateController::class, 'update']);
            Route::delete('/{id}', [CertificateController::class, 'destroy']);
        });

        // Documents API
        Route::prefix('documents')->group(function () {
            Route::get('/', [DocumentController::class, 'index']);
            Route::post('/', [DocumentController::class, 'store']);
            Route::get('/statistics', [DocumentController::class, 'statistics']);
            Route::get('/due-for-review', [DocumentController::class, 'dueForReview']);
            Route::get('/{id}', [DocumentController::class, 'show']);
            Route::put('/{id}', [DocumentController::class, 'update']);
            Route::patch('/{id}', [DocumentController::class, 'update']);
            Route::delete('/{id}', [DocumentController::class, 'destroy']);
        });

        // Complaints API
        Route::prefix('complaints')->group(function () {
            Route::get('/', [ComplaintController::class, 'index']);
            Route::post('/', [ComplaintController::class, 'store']);
            Route::get('/statistics', [ComplaintController::class, 'statistics']);
            Route::get('/unresolved', [ComplaintController::class, 'unresolved']);
            Route::get('/{id}', [ComplaintController::class, 'show']);
            Route::put('/{id}', [ComplaintController::class, 'update']);
            Route::patch('/{id}', [ComplaintController::class, 'update']);
            Route::delete('/{id}', [ComplaintController::class, 'destroy']);
        });
    });
});

// API Health Check
Route::get('/health', function () {
    return response()->json([
        'success' => true,
        'message' => 'API is running',
        'version' => 'v1',
        'timestamp' => now()->toIso8601String(),
    ]);
});
