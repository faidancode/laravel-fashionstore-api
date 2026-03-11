<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DashboardRepository
{
    /**
     * Mengambil statistik ringkasan dashboard.
     */
    public function getStats(): object
    {
        return (object) [
            'total_products'   => Product::count(),
            'total_brands'     => Brand::count(),
            'total_categories' => Category::count(),
            'total_customers'  => User::where('role', 'CUSTOMER')->count(),
            'total_orders'     => Order::count(),
            'total_revenue'    => Order::where('status', 'COMPLETED')->sum('total_price'),
        ];
    }

    /**
     * Mengambil daftar pesanan terbaru beserta nama user.
     */
    public function listRecentOrders(int $limit): Collection
    {
        return Order::with('user:id,name')
            ->select(['id', 'order_number', 'total_price', 'status', 'user_id', 'placed_at'])
            ->orderByDesc('placed_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Mengambil distribusi jumlah produk per kategori.
     */
    public function getCategoryDistribution(): Collection
    {
        return Category::withCount(['products' => function ($query) {
            $query->whereNull('deleted_at'); // Mengamankan jika SoftDeletes aktif
        }])
            ->orderByDesc('products_count')
            ->get(['id', 'name'])
            ->map(fn($category) => (object) [
                'category_name' => $category->name,
                'product_count' => $category->products_count,
            ]);
    }
}
