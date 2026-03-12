<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\Auth\RegisterDTO;
use App\Mail\EmailConfirmationMail;
use App\Mail\PasswordResetMail;
use App\Repositories\AuthRepository;
use App\Models\User;
use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Resend\Facades\Resend;
use Resend\Laravel\Facades\Resend as FacadesResend;
use Tymon\JWTAuth\JWTGuard;

class AuthService
{
    public function __construct(
        protected AuthRepository $authRepo
    ) {}

    /**
     * Mendaftarkan User baru sekaligus membuat token konfirmasi email.
     */
    public function register(RegisterDTO $data): User
    {
        // Business Validation: Cek apakah email/telepon sudah dipakai
        if ($this->authRepo->getByEmail($data->email)) {
            throw new Exception("Email sudah terdaftar.");
        }

        if ($data->phone && $this->authRepo->checkPhoneExists($data->phone)) {
            throw new Exception("Nomor telepon sudah digunakan.");
        }

        return DB::transaction(function () use ($data) {
            // Hash password di Service layer sesuai instruksi repo
            $payload = [
                'email' => $data->email,
                'name' => $data->name,
                'phone' => $data->phone,
                'password' => Hash::make($data->password),
                'is_active' => true,
            ];

            $user = $this->authRepo->create($payload);

            // Generate Email Confirmation Token otomatis setelah registrasi
            $userId = (string) $user->id;
            $confirmation = $this->generateEmailConfirmation($userId);
            $this->sendEmailConfirmation($user, $confirmation);

            return $user;
        });
    }

    /**
     * Mengelola pembuatan token konfirmasi email (PIN & Token).
     */
    public function generateEmailConfirmation(string $userId): array
    {
        $token = Str::random(64);
        $pin = (string) random_int(100000, 999999);
        $expiresAt = now()->addHours(24);

        $this->authRepo->upsertEmailConfirmationToken([
            'user_id'    => $userId,
            'token'      => $token,
            'pin'        => $pin,
            'expires_at' => $expiresAt,
            'created_at' => now(),
        ]);

        return [
            'token' => $token,
            'pin' => $pin,
            'expires_at' => $expiresAt,
        ];
    }

    /**
     * Verifikasi email via Token (biasanya dari link email).
     */
    public function confirmEmailByToken(string $token): void
    {
        $record = $this->authRepo->getEmailConfirmationTokenByToken($token);

        if (!$record || now()->greaterThan($record->expires_at)) {
            throw new Exception("Token konfirmasi tidak valid atau sudah kedaluwarsa.");
        }

        DB::transaction(function () use ($record) {
            $this->authRepo->setUserEmailConfirmed($record->user_id);
            $this->authRepo->deleteEmailConfirmationTokenByToken($record->token);
        });
    }

    /**
     * Memulai alur Lupa Password.
     */
    public function requestPasswordReset(string $email): void
    {
        $user = $this->authRepo->getByEmail($email);

        if ($user) {
            $token = Str::random(64);
            $expiresAt = Carbon::instance(now()->addMinutes(60));

            $this->authRepo->upsertPasswordResetToken([
                'user_id'    => $user->id,
                'token'      => $token,
                'expires_at' => $expiresAt,
                'created_at' => now(),
            ]);

            $this->sendPasswordResetEmail($user, $token, $expiresAt);
        }
    }

    /**
     * Reset password menggunakan token baru.
     */
    public function resetPassword(string $token, string $newPassword): void
    {
        $resetRecord = $this->authRepo->getPasswordResetToken($token);

        if (!$resetRecord || now()->greaterThan($resetRecord->expires_at)) {
            throw new Exception("Token reset password tidak valid atau kedaluwarsa.");
        }

        DB::transaction(function () use ($resetRecord, $newPassword) {
            $hashedPassword = Hash::make($newPassword);
            $this->authRepo->updateUserPassword($resetRecord->user_id, $hashedPassword);
            $this->authRepo->deletePasswordResetTokenByToken($resetRecord->token);
        });
    }

    /**
     * Logika Login (Email & Password).
     */
    public function login(string $email, string $password): array
    {
        $user = $this->authRepo->getByEmail($email);

        if (!$user || !Hash::check($password, $user->password)) {
            throw new Exception("Kredensial salah.");
        }

        if (!$user->email_confirmed) {
            throw new Exception("Email belum dikonfirmasi.");
        }

        /** @var JWTGuard $guard */
        $guard = auth('api');

        $accessToken = $guard->claims(['role' => $user->role])->login($user);

        if (!$accessToken) {
            throw new Exception("Gagal membuat token akses.");
        }

        $refreshToken = Str::random(64);

        $this->authRepo->updateRefreshToken($user->id, $refreshToken);

        $ttl = $guard->factory()->getTTL() * 60;

        return [
            'user' => $user,
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
            'expires_in' => $ttl
        ];
    }

    public function refreshToken(string $oldRefreshToken): array
    {
        // 1. Cari user berdasarkan token yang ada di database
        $user = $this->authRepo->getUserByRefreshToken($oldRefreshToken);

        if (!$user) {
            throw new Exception("Refresh token tidak valid.");
        }

        // 2. Generate Access Token baru
        /** @var JWTGuard $guard */
        $guard = auth('api');
        $newAccessToken = $guard->login($user);

        // 3. (Opsional) Rotate Refresh Token (Ganti refresh token lama dengan yang baru setiap kali digunakan)
        $newRefreshToken = Str::random(64);
        $this->authRepo->updateRefreshToken($user->id, $newRefreshToken);

        return [
            'access_token' => $newAccessToken,
            'refresh_token' => $newRefreshToken,
            'expires_in' => $guard->factory()->getTTL() * 60
        ];
    }

    protected function sendEmailConfirmation(User $user, array $confirmation): void
    {
        FacadesResend::emails()->send([
            'from' => sprintf('%s <%s>', config('mail.from.name'), config('mail.from.address')),
            'to' => [$user->email],
            'subject' => 'Konfirmasi Email',
            'html' => (new EmailConfirmationMail(
                $user->name,
                $confirmation['token'],
                $confirmation['pin'],
                $confirmation['expires_at']
            ))->render(),
        ]);
    }

    protected function sendPasswordResetEmail(User $user, string $token, Carbon $expiresAt): void
    {
        FacadesResend::emails()->send([
            'from' => sprintf('%s <%s>', config('mail.from.name'), config('mail.from.address')),
            'to' => [$user->email],
            'subject' => 'Reset Password',
            'html' => (new PasswordResetMail(
                $user->name,
                $token,
                $expiresAt
            ))->render(),
        ]);
    }
}
