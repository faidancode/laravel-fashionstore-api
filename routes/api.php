<?php

use App\Http\Controllers\Api\v1\AddressController;
use App\Http\Controllers\Api\v1\AuthController;
use App\Http\Controllers\Api\v1\BrandController;
use App\Http\Controllers\Api\v1\CategoryController;
use App\Http\Controllers\Api\v1\ProductController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    Route::prefix('auth')->group(function () {
        // Rate Limit Ketat: Login & Register (5 request / menit)
        Route::middleware('throttle:5,1')->group(function () {
            Route::post('/register', [AuthController::class, 'register']);
            Route::post('/login', [AuthController::class, 'login']);
        });

        // Rate Limit Sedang: Reset & Confirm (60 request / menit)
        Route::middleware('throttle:60,1')->group(function () {
            Route::post('/confirm-email', [AuthController::class, 'confirmEmail']);
            Route::post('/forgot-password', [AuthController::class, 'requestPasswordReset']);
            Route::post('/reset-password', [AuthController::class, 'resetPassword']);
            Route::post('/refresh', [AuthController::class, 'refresh']);
        });

        Route::middleware(['auth:api', 'throttle:60,1'])->group(function () {
            Route::post('/logout', [AuthController::class, 'logout']);
        });
    });

    Route::middleware(['auth:api'])->prefix('addresses')->group(function () {

        // Read Access (Tanpa throttle ketat)
        Route::get('/', [AddressController::class, 'index']);
        Route::get('/{id}', [AddressController::class, 'show']);

        // Write Access: Proteksi tambahan (10x per menit)
        Route::middleware('throttle:10,1')->group(function () {
            Route::post('/', [AddressController::class, 'store']);
            Route::put('/{id}', [AddressController::class, 'update']);
            Route::patch('/{id}/set-primary', [AddressController::class, 'setPrimary']);
            Route::delete('/{id}', [AddressController::class, 'destroy']);
        });
    });

    Route::middleware(['auth:api'])->prefix('brands')->group(function () {

        // Read Access (Tanpa throttle ketat)
        Route::get('/', [BrandController::class, 'index']);
        Route::get('/{id}', [BrandController::class, 'show']);

        // Write Access: Proteksi tambahan (10x per menit)
        Route::middleware('throttle:10,1')->group(function () {
            Route::post('/', [BrandController::class, 'store']);
            Route::put('/{id}', [BrandController::class, 'update']);
            Route::delete('/{id}', [BrandController::class, 'destroy']);
        });
    });

    Route::middleware(['auth:api'])->prefix('categories')->group(function () {

        // Read Access (Tanpa throttle ketat)
        Route::get('/', [CategoryController::class, 'index']);
        Route::get('/{id}', [CategoryController::class, 'show']);

        // Write Access: Proteksi tambahan (10x per menit)
        Route::middleware('throttle:10,1')->group(function () {
            Route::post('/', [CategoryController::class, 'store']);
            Route::put('/{id}', [CategoryController::class, 'update']);
            Route::delete('/{id}', [CategoryController::class, 'destroy']);
        });
    });

    Route::middleware(['auth:api'])->prefix('products')->group(function () {

        // Read Access (Tanpa throttle ketat)
        Route::get('/', [ProductController::class, 'index']);
        Route::get('/{id}', [ProductController::class, 'show']);

        // Write Access: Proteksi tambahan (10x per menit)
        Route::middleware('throttle:10,1')->group(function () {
            Route::post('/', [ProductController::class, 'store']);
            Route::put('/{id}', [ProductController::class, 'update']);
            Route::delete('/{id}', [ProductController::class, 'destroy']);
        });
    });
});
