<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\CartRepository;
use App\Repositories\ProductRepository;
use Exception;
use Illuminate\Support\Collection;

class CartService
{
    public function __construct(
        protected CartRepository $cartRepo,
        protected ProductRepository $productRepo
    ) {
    }

    /**
     * Mengambil detail isi keranjang user.
     */
    public function getCartDetail(string $userId): Collection
    {
        return $this->cartRepo->getDetail($userId);
    }

    /**
     * Menambahkan item ke cart.
     */
    public function addItem(string $userId, string $productId, int $quantity): Collection
    {
        $cart = $this->cartRepo->createCart($userId);
        $product = $this->productRepo->getById($productId);

        if (!$product) {
            throw new Exception('Produk tidak ditemukan.');
        }

        $this->cartRepo->addItem([
            'cart_id' => $cart->id,
            'product_id' => $productId,
            'quantity' => $quantity,
            'price_at_add' => (int) $product->price,
        ]);

        return $this->cartRepo->getDetail($userId);
    }

    /**
     * Update quantity item dalam cart.
     */
    public function updateQty(string $userId, string $productId, int $quantity): Collection
    {
        $cart = $this->cartRepo->getByUserId($userId);
        if (!$cart) {
            throw new Exception('Cart tidak ditemukan.');
        }

        $item = $this->cartRepo->updateQty([
            'cart_id' => $cart->id,
            'product_id' => $productId,
            'quantity' => $quantity,
        ]);

        if (!$item) {
            throw new Exception('Item tidak ditemukan.');
        }

        return $this->cartRepo->getDetail($userId);
    }

    public function incrementQty(string $userId, string $productId): Collection
    {
        $cart = $this->cartRepo->getByUserId($userId);
        if (!$cart) {
            throw new Exception('Cart tidak ditemukan.');
        }

        $item = $this->cartRepo->incrementQty($cart->id, $productId);
        if (!$item) {
            throw new Exception('Item tidak ditemukan.');
        }

        return $this->cartRepo->getDetail($userId);
    }

    public function decrementQty(string $userId, string $productId): Collection
    {
        $cart = $this->cartRepo->getByUserId($userId);
        if (!$cart) {
            throw new Exception('Cart tidak ditemukan.');
        }

        $item = $this->cartRepo->decrementQty($cart->id, $productId);
        if (!$item) {
            throw new Exception('Item tidak ditemukan.');
        }

        return $this->cartRepo->getDetail($userId);
    }

    public function deleteItem(string $userId, string $productId): Collection
    {
        $cart = $this->cartRepo->getByUserId($userId);
        if (!$cart) {
            throw new Exception('Cart tidak ditemukan.');
        }

        $item = $this->cartRepo->getItemByCartAndProduct($cart->id, $productId);
        if (!$item) {
            throw new Exception('Item tidak ditemukan.');
        }

        $this->cartRepo->deleteItem($cart->id, $productId);

        return $this->cartRepo->getDetail($userId);
    }

    public function clearCart(string $userId): Collection
    {
        $cart = $this->cartRepo->getByUserId($userId);
        if (!$cart) {
            return collect([]);
        }

        $this->cartRepo->deleteAllItems($cart->id);

        return $this->cartRepo->getDetail($userId);
    }
}
