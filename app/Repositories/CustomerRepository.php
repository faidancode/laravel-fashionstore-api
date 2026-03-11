<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

class CustomerRepository
{
    /**
     * Mendapatkan data user berdasarkan ID.
     */
    public function getById(string $id): ?User
    {
        return User::find($id);
    }

    /**
     * Update profil customer (Nama).
     */
    public function updateProfile(array $params): ?User
    {
        $user = User::where('id', $params['id'])
            ->where('role', 'CUSTOMER')
            ->first();

        if ($user) {
            $user->update([
                'name' => $params['name']
            ]);
        }

        return $user;
    }

    /**
     * Update password customer.
     */
    public function updatePassword(array $params): void
    {
        User::where('id', $params['id'])
            ->where('role', 'CUSTOMER')
            ->update([
                'password' => $params['password'],
                'updated_at' => now(),
            ]);
    }

    /**
     * Daftar customer untuk admin dengan fitur search.
     */
    public function listCustomers(array $params): LengthAwarePaginator
    {
        $limit = (int) ($params['limit'] ?? 10);
        $offset = (int) ($params['offset'] ?? 0);

        return User::query()
            ->where('role', 'CUSTOMER')
            ->when(!empty($params['search']), function ($query) use ($params) {
                $search = $params['search'];
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'ilike', "%{$search}%")
                        ->orWhere('email', 'ilike', "%{$search}%");
                });
            })
            ->orderByDesc('created_at')
            ->paginate($limit, ['id', 'name', 'email', 'phone', 'is_active', 'created_at'], 'page', (int)($offset / $limit) + 1);
    }

    /**
     * Update status aktif/non-aktif customer.
     */
    public function updateStatus(array $params): ?User
    {
        $user = User::where('id', $params['id'])
            ->where('role', 'CUSTOMER')
            ->first();

        if ($user) {
            $user->update([
                'is_active' => $params['is_active']
            ]);
        }

        return $user;
    }
}
