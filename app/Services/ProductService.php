<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\ProductRepository;
use Exception;

class ProductService
{
    public function __construct(
        protected ProductRepository $productRepo
    ) {
    }

    /**
     * Mengambil daftar produk dengan pagination.
     */
    public function getProducts(array $params)
    {
        return $this->productRepo->getPaginated($params);
    }

    /**
     * Mengambil detail produk berdasarkan ID.
     */
    public function getProductDetail(string $id)
    {
        $product = $this->productRepo->getById($id);
        if (!$product) {
            throw new Exception('Produk tidak ditemukan.');
        }
        return $product;
    }

    /**
     * Membuat produk baru.
     */
    public function createProduct(array $data)
    {
        return $this->productRepo->create($data);
    }

    /**
     * Update produk.
     */
    public function updateProduct(string $id, array $data)
    {
        $this->getProductDetail($id);
        $data['id'] = $id;
        return $this->productRepo->update($data);
    }

    /**
     * Menghapus produk.
     */
    public function deleteProduct(string $id): void
    {
        $this->productRepo->delete($id);
    }
}
