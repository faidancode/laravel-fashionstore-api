<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\BrandRepository;
use Exception;

class BrandService
{
    public function __construct(
        protected BrandRepository $brandRepo
    ) {
    }

    /**
     * Mengambil daftar brand dengan pagination.
     */
    public function getBrands(array $params)
    {
        return $this->brandRepo->getPaginated($params);
    }

    /**
     * Mengambil detail brand berdasarkan ID.
     */
    public function getBrandDetail(string $id)
    {
        $brand = $this->brandRepo->getById($id);
        if (!$brand) {
            throw new Exception('Brand tidak ditemukan.');
        }
        return $brand;
    }

    /**
     * Membuat brand baru.
     */
    public function createBrand(array $data)
    {
        return $this->brandRepo->create($data);
    }

    /**
     * Update brand.
     */
    public function updateBrand(string $id, array $data)
    {
        $this->getBrandDetail($id);
        return $this->brandRepo->update($id, $data);
    }

    /**
     * Menghapus brand.
     */
    public function deleteBrand(string $id): void
    {
        $this->brandRepo->delete($id);
    }
}
