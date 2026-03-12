<?php

// app/Http/Controllers/Api/Auth/AuthController.php
namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Services\AuthService;
use App\DTOs\Auth\RegisterDTO;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    public function __construct(
        protected AuthService $authService
    ) {}

    public function register(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'name' => 'required|string|max:255',
            'password' => 'required|min:8|confirmed',
            'phone' => 'nullable|string'
        ]);

        $dto = RegisterDTO::fromRequest($request);
        $user = $this->authService->register($dto);

        return response()->json([
            'message' => 'Registrasi berhasil, silakan cek email untuk verifikasi.',
            'data' => $user
        ], 201);
    }

    public function login(Request $request): JsonResponse
    {
        // 1. Validasi Input
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        try {
            // 2. Panggil Service untuk Logika Otentikasi
            $result = $this->authService->login(
                $credentials['email'],
                $credentials['password']
            );

            // 3. Buat HttpOnly Cookie untuk Refresh Token
            // 20160 menit = 14 hari
            $cookie = cookie(
                'refresh_token',
                $result['refresh_token'],
                20160,
                '/',
                null,
                config('app.env') === 'production', // Secure: true jika di production
                true, // HttpOnly: Tidak bisa diakses JavaScript
                false,
                'Lax'
            );

            // 4. Return Response dengan Access Token
            return response()->json([
                'status'  => 'success',
                'message' => 'Login berhasil.',
                'data'    => [
                    'user'         => $result['user'],
                    'access_token' => $result['access_token'],
                    'token_type'   => 'Bearer',
                    'expires_in'   => $result['expires_in'],
                ]
            ], 200)->withCookie($cookie);
        } catch (\Exception $e) {
            // Tangani error kredensial atau email belum dikonfirmasi
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage()
            ], 401);
        }
    }

    public function confirmEmail(Request $request): JsonResponse
    {
        $request->validate(['token' => 'required']);

        try {
            $this->authService->confirmEmailByToken($request->token);
            return response()->json(['message' => 'Email berhasil dikonfirmasi.']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    public function refresh(Request $request): JsonResponse
    {
        $refreshToken = $request->cookie('refresh_token');

        if (!$refreshToken) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        try {
            $result = $this->authService->refreshToken($refreshToken);

            // Set Cookie baru jika Anda melakukan rotasi token
            $cookie = cookie('refresh_token', $result['refresh_token'], 20160, '/', null, true, true);

            return response()->json([
                'access_token' => $result['access_token'],
                'token_type' => 'Bearer',
                'expires_in' => $result['expires_in']
            ])->withCookie($cookie);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 401);
        }
    }
}
