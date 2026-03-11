<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Review;
use App\Models\Order;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class ReviewRepository
{
    public function create(array $params): Review
    {
        return Review::create($params);
    }

    public function getById(string $id): ?Review
    {
        return Review::with('user:id,name,email')->find($id);
    }

    /**
     * Mengambil ulasan berdasarkan Produk (untuk halaman produk).
     */
    public function getByProductId(string $productId, int $limit, int $offset): LengthAwarePaginator
    {
        return Review::with('user:id,name')
            ->where('product_id', $productId)
            ->orderByDesc('created_at')
            ->paginate($limit, ['*'], 'page', (int)($offset / $limit) + 1);
    }

    /**
     * Mengambil ulasan berdasarkan User (untuk halaman profil).
     */
    public function getByUserId(string $userId, int $limit, int $offset): LengthAwarePaginator
    {
        return Review::with('product:id,name,slug')
            ->where('user_id', $userId)
            ->orderByDesc('created_at')
            ->paginate($limit, ['*'], 'page', (int)($offset / $limit) + 1);
    }

    public function getAverageRating(string $productId): float
    {
        return (float) Review::where('product_id', $productId)->avg('rating') ?? 0.0;
    }

    public function checkExists(string $userId, string $productId): bool
    {
        return Review::where('user_id', $userId)
            ->where('product_id', $productId)
            ->exists();
    }

    /**
     * Memeriksa apakah user benar-benar telah membeli produk tersebut.
     */
    public function checkUserPurchased(string $userId, string $productId): bool
    {
        return Order::where('user_id', $userId)
            ->where('status', 'COMPLETED')
            ->whereHas('items', fn($q) => $q->where('product_id', $productId))
            ->exists();
    }

    /**
     * Mendapatkan ID order terakhir yang sukses untuk produk ini.
     */
    public function getCompletedOrder(string $userId, string $productId): ?Order
    {
        return Order::where('user_id', $userId)
            ->where('status', 'COMPLETED')
            ->whereHas('items', fn($q) => $q->where('product_id', $productId))
            ->orderByDesc('completed_at')
            ->first(['id']);
    }

    public function update(array $params): ?Review
    {
        $review = Review::find($params['id']);
        if ($review) {
            $review->update([
                'rating' => $params['rating'],
                'comment' => $params['comment'],
            ]);
        }
        return $review;
    }

    public function delete(string $id): void
    {
        Review::destroy($id);
    }

    /**
     * Mendapatkan sebaran rating (1-5 star) untuk seorang user.
     */
    public function getUserRatingBreakdown(string $userId): Collection
    {
        return Review::where('user_id', $userId)
            ->selectRaw('rating, COUNT(*) as count')
            ->groupBy('rating')
            ->get();
    }
}
