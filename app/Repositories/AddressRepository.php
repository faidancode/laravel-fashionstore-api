<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Address;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class AddressRepository
{
    /**
     * Mengambil daftar alamat berdasarkan User ID dengan pagination.
     */
    public function listByUser(array $params): LengthAwarePaginator
    {
        return Address::query()
            ->where('user_id', $params['user_id'])
            ->when(!empty($params['search']), function ($query) use ($params) {
                $search = $params['search'];
                $query->where(function ($q) use ($search) {
                    $q->where('label', 'ilike', "%{$search}%")
                        ->orWhere('recipient_name', 'ilike', "%{$search}%")
                        ->orWhere('street', 'ilike', "%{$search}%");
                });
            })
            ->orderByDesc('is_primary')
            ->orderByDesc('created_at')
            ->paginate($params['limit'] ?? 10, ['*'], 'page', ($params['offset'] / ($params['limit'] ?? 10)) + 1);
    }

    public function getById(string $id, string $userId): ?Address
    {
        return Address::where('id', $id)
            ->where('user_id', $userId)
            ->first();
    }

    public function create(array $params): Address
    {
        return Address::create($params);
    }

    public function update(string $id, array $params): ?Address
    {
        $address = Address::where('id', $id)->first();

        if ($address) {
            $address->update($params);
        }

        return $address;
    }

    public function delete(string $id, string $userId): bool
    {
        return (bool) Address::where('id', $id)
            ->where('user_id', $userId)
            ->delete(); // Ini otomatis melakukan Soft Delete jika trait digunakan
    }

    public function unsetPrimaryByUser(string $userId): void
    {
        Address::where('user_id', $userId)
            ->where('is_primary', true)
            ->update(['is_primary' => false]);
    }

    /**
     * Versi Admin dengan relasi User.
     */
    public function listAdmin(int $limit = 10): LengthAwarePaginator
    {
        return Address::with('user:id,email')
            ->orderByDesc('created_at')
            ->paginate($limit);
    }
}
