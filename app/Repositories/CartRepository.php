<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Cart;
use App\Models\CartItem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CartRepository
{
    /**
     * Membuat atau mengambil keranjang belanja user.
     */
    public function createCart(string $userId): Cart
    {
        return Cart::firstOrCreate(['user_id' => $userId]);
    }

    public function getByUserId(string $userId): ?Cart
    {
        return Cart::where('user_id', $userId)->first();
    }

    /**
     * Menghitung total quantity item di dalam cart.
     */
    public function count(string $cartId): int
    {
        return (int) CartItem::where('cart_id', $cartId)->sum('quantity');
    }

    /**
     * Mengambil detail isi keranjang beserta data produk.
     */
    public function getDetail(string $userId): Collection
    {
        // Menggunakan Eager Loading agar tidak terjadi N+1 query
        $cart = Cart::with(['items.product'])
            ->where('user_id', $userId)
            ->first();

        if (!$cart) return collect([]);

        return $cart->items->map(function ($item) {
            return (object) [
                'id'                 => $item->id,
                'product_id'         => $item->product_id,
                'product_name'       => $item->product->name,
                'product_slug'       => $item->product->slug,
                'product_image_url'  => $item->product->image_url,
                'quantity'           => $item->quantity,
                'price_at_add'       => $item->price_at_add,
                'created_at'         => $item->created_at,
            ];
        })->sortByDesc('created_at')->values();
    }

    public function getItemByCartAndProduct(string $cartId, string $productId): ?CartItem
    {
        return CartItem::where('cart_id', $cartId)
            ->where('product_id', $productId)
            ->first();
    }

    /**
     * Menambahkan item ke cart. Jika produk sudah ada, quantity akan ditambah.
     */
    public function addItem(array $params): void
    {
        $item = CartItem::where('cart_id', $params['cart_id'])
            ->where('product_id', $params['product_id'])
            ->first();

        if ($item) {
            $item->increment('quantity', $params['quantity']);
        } else {
            CartItem::create($params);
        }
    }

    public function updateQty(array $params): ?CartItem
    {
        $item = $this->getItemByCartAndProduct($params['cart_id'], $params['product_id']);

        if ($item) {
            $item->update(['quantity' => $params['quantity']]);
        }

        return $item;
    }

    public function incrementQty(string $cartId, string $productId): ?CartItem
    {
        $item = $this->getItemByCartAndProduct($cartId, $productId);

        if ($item) {
            $item->increment('quantity');
        }

        return $item;
    }

    public function decrementQty(string $cartId, string $productId): ?CartItem
    {
        $item = $this->getItemByCartAndProduct($cartId, $productId);

        if ($item && $item->quantity > 0) {
            $item->decrement('quantity');
        }

        return $item;
    }

    public function deleteItem(string $cartId, string $productId): void
    {
        CartItem::where('cart_id', $cartId)
            ->where('product_id', $productId)
            ->delete();
    }

    public function delete(string $cartId): void
    {
        Cart::where('id', $cartId)->delete();
    }

    public function deleteAllItems(string $cartId): void
    {
        CartItem::where('cart_id', $cartId)->delete();
    }
}
