<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\CategoryRepository;
use Exception;

class CategoryService
{
    public function __construct(
        protected CategoryRepository $categoryRepo
    ) {
    }

    /**
     * Mengambil daftar kategori dengan pagination.
     */
    public function getCategories(array $params)
    {
        return $this->categoryRepo->getPaginated($params);
    }

    /**
     * Mengambil detail kategori berdasarkan ID.
     */
    public function getCategoryDetail(string $id)
    {
        $category = $this->categoryRepo->getById($id);
        if (!$category) {
            throw new Exception('Kategori tidak ditemukan.');
        }
        return $category;
    }

    /**
     * Membuat kategori baru.
     */
    public function createCategory(array $data)
    {
        return $this->categoryRepo->create($data);
    }

    /**
     * Update kategori.
     */
    public function updateCategory(string $id, array $data)
    {
        $this->getCategoryDetail($id);
        return $this->categoryRepo->update($id, $data);
    }

    /**
     * Menghapus kategori.
     */
    public function deleteCategory(string $id): void
    {
        $this->categoryRepo->delete($id);
    }
}
