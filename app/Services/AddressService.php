<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\AddressRepository;
use Exception;
use Illuminate\Support\Facades\DB;

class AddressService
{
    public function __construct(
        protected AddressRepository $addressRepo
    ) {
    }

    /**
     * Mengambil semua alamat milik user tertentu.
     */
    public function getUserAddresses(string $userId, array $params)
    {
        // Pastikan memanggil method repository yang mendukung paginasi dan filter
        return $this->addressRepo->getPaginatedByUserId($userId, $params);
    }

    /**
     * Mengambil detail alamat berdasarkan ID.
     */
    public function getAddressDetail(string $id)
    {
        $address = $this->addressRepo->getById($id);
        if (!$address) {
            throw new Exception("Alamat tidak ditemukan.");
        }
        return $address;
    }

    /**
     * Menambah alamat baru. 
     * Jika alamat ditandai sebagai 'is_primary', maka alamat lain harus di-unset.
     */
    public function createAddress(array $data)
    {
        return DB::transaction(function () use ($data) {
            // Jika alamat baru ini adalah utama, matikan status utama alamat lama
            if (!empty($data['is_primary']) && $data['is_primary'] === true) {
                $this->addressRepo->unsetPrimaryByUser($data['user_id']);
            }

            return $this->addressRepo->create($data);
        });
    }

    /**
     * Update alamat yang sudah ada.
     */
    public function updateAddress(string $id, array $data)
    {
        return DB::transaction(function () use ($id, $data) {
            $address = $this->getAddressDetail($id);

            // Jika update status menjadi primary
            if (!empty($data['is_primary']) && $data['is_primary'] === true) {
                $this->addressRepo->unsetPrimaryByUser($address->user_id);
            }

            return $this->addressRepo->update($id, $data);
        });
    }

    /**
     * Menghapus alamat.
     */
    public function deleteAddress(string $id): void
    {
        $this->addressRepo->delete($id);
    }

    /**
     * Mengatur alamat tertentu menjadi alamat utama secara manual.
     */
    public function setAsPrimary(string $userId, string $addressId)
    {
        return DB::transaction(function () use ($userId, $addressId) {
            $this->addressRepo->unsetPrimaryByUser($userId);
            return $this->addressRepo->update($addressId, ['is_primary' => true]);
        });
    }
}
