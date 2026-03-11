<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Wishlist;
use App\Models\WishlistItem;
use Illuminate\Support\Facades\DB;

class WishlistRepository
{
    /**
     * Mendapatkan wishlist atau membuat baru jika belum ada.
     */
    public function getOrCreateWishlist(string $userId): Wishlist
    {
        return Wishlist::firstOrCreate(
            ['user_id' => $userId],
            ['updated_at' => now()]
        );
    }

    public function getWishlistByUserId(string $userId): ?Wishlist
    {
        return Wishlist::where('user_id', $userId)->first();
    }

    /**
     * Mengambil wishlist lengkap dengan produk dan kategorinya.
     */
    public function getWishlistWithItems(string $userId): ?Wishlist
    {
        return Wishlist::with(['items.product.category'])
            ->where('user_id', $userId)
            ->first();
    }

    /**
     * Menambah item ke wishlist dengan proteksi duplikasi (ON CONFLICT).
     */
    public function addItem(string $wishlistId, string $productId): void
    {
        WishlistItem::updateOrInsert(
            ['wishlist_id' => $wishlistId, 'product_id' => $productId],
            ['created_at' => now()]
        );
    }

    /**
     * Mendapatkan daftar item dalam wishlist tertentu.
     */
    public function getItems(string $wishlistId)
    {
        return WishlistItem::with('product')
            ->where('wishlist_id', $wishlistId)
            ->orderByDesc('created_at')
            ->get();
    }

    public function deleteItem(string $wishlistId, string $productId): void
    {
        WishlistItem::where('wishlist_id', $wishlistId)
            ->where('product_id', $productId)
            ->delete();
    }

    public function checkItemExists(string $wishlistId, string $productId): bool
    {
        return WishlistItem::where('wishlist_id', $wishlistId)
            ->where('product_id', $productId)
            ->exists();
    }
}
