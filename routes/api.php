<?php

// routes/api.php

use App\Http\Controllers\Api\v1\AuthController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1/auth')->group(function () {

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

        // Refresh token biasanya dipanggil otomatis oleh interceptor frontend, 
        // jadi 60/menit adalah angka yang aman.
        Route::post('/refresh', [AuthController::class, 'refresh']);
    });

    // Logout: Hanya bisa diakses jika sudah login
    Route::middleware(['auth:api', 'throttle:60,1'])->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
    });
});
