<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\User;
use App\Models\PasswordResetToken;
use App\Models\EmailConfirmationToken;
use Illuminate\Support\Facades\DB;

class AuthRepository
{
    /**
     * Membuat user baru.
     */
    public function create(array $params): User
    {
        return User::create([
            'email'    => $params['email'],
            'name'     => $params['name'],
            'phone'    => $params['phone'] ?? null,
            'is_active'    => $params['is_active'] ?? true,
            'password' => $params['password'], // Pastikan sudah di-hash di Service layer
            'role'     => $params['role'],
        ]);
    }

    public function getByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    public function getById(string $id): ?User
    {
        return User::find($id);
    }

    public function checkPhoneExists(?string $phone): bool
    {
        if (!$phone) return false;
        return User::where('phone', $phone)->exists();
    }

    // --- Password Reset Section ---

    public function getLatestPasswordResetTokenByUserId(string $userId): ?PasswordResetToken
    {
        return PasswordResetToken::where('user_id', $userId)
            ->orderByDesc('created_at')
            ->first();
    }

    public function upsertPasswordResetToken(array $params): void
    {
        PasswordResetToken::updateOrInsert(
            ['user_id' => $params['user_id']],
            [
                'token'      => $params['token'],
                'expires_at' => $params['expires_at'],
                'created_at' => $params['created_at'],
            ]
        );
    }

    public function getPasswordResetToken(string $token): ?PasswordResetToken
    {
        return PasswordResetToken::where('token', $token)->first();
    }

    public function deletePasswordResetTokenByToken(string $token): void
    {
        PasswordResetToken::where('token', $token)->delete();
    }

    public function updateUserPassword(string $userId, string $password): void
    {
        User::where('id', $userId)
            ->where('role', 'CUSTOMER')
            ->update([
                'password' => $password,
                'updated_at' => now(),
            ]);
    }

    // --- Email Confirmation Section ---

    public function getLatestEmailConfirmationTokenByUserId(string $userId): ?EmailConfirmationToken
    {
        return EmailConfirmationToken::where('user_id', $userId)
            ->orderByDesc('created_at')
            ->first();
    }

    public function upsertEmailConfirmationToken(array $params): void
    {
        EmailConfirmationToken::updateOrInsert(
            ['user_id' => $params['user_id']],
            [
                'token'      => $params['token'],
                'pin'        => $params['pin'],
                'expires_at' => $params['expires_at'],
                'created_at' => $params['created_at'],
            ]
        );
    }

    public function deleteEmailConfirmationTokensByUserId(string $userId): void
    {
        EmailConfirmationToken::where('user_id', $userId)->delete();
    }

    public function getEmailConfirmationTokenByToken(string $token): ?EmailConfirmationToken
    {
        return EmailConfirmationToken::where('token', $token)->first();
    }

    public function deleteEmailConfirmationTokenByToken(string $token): void
    {
        EmailConfirmationToken::where('token', $token)->delete();
    }

    public function deleteEmailConfirmationTokenByPin(string $pin): void
    {
        EmailConfirmationToken::where('pin', $pin)->delete();
    }

    public function setUserEmailConfirmed(string $userId): void
    {
        User::where('id', $userId)->update([
            'email_confirmed' => true,
            'updated_at'      => now(),
        ]);
    }
}
