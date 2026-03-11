<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Product;
use Illuminate\Pagination\LengthAwarePaginator;

class ProductRepository
{
    public function create(array $params): Product
    {
        return Product::create($params);
    }

    /**
     * List produk untuk halaman depan (Public).
     */
    public function listPublic(array $params): LengthAwarePaginator
    {
        $limit = (int) ($params['limit'] ?? 10);
        $offset = (int) ($params['offset'] ?? 0);

        return Product::query()
            ->with(['category', 'brand'])
            ->where('is_active', true)
            ->when(!empty($params['category_ids']), function ($q) use ($params) {
                $q->whereIn('category_id', $params['category_ids']);
            })
            ->when(!empty($params['search']), function ($q) use ($params) {
                $q->where('name', 'ilike', "%{$params['search']}%");
            })
            ->when(!empty($params['brand_slug']), function ($q) use ($params) {
                $q->whereHas('brand', fn($b) => $b->where('slug', $params['brand_slug']));
            })
            ->whereBetween('price', [
                $params['min_price'] ?? 0,
                $params['max_price'] ?? PHP_FLOAT_MAX
            ])
            ->when($params['sort_by'] ?? null, function ($q, $sort) {
                match ($sort) {
                    'oldest' => $q->orderBy('created_at', 'asc'),
                    'price_high' => $q->orderBy('price', 'desc'),
                    'price_low' => $q->orderBy('price', 'asc'),
                    default => $q->orderByDesc('created_at'),
                };
            }, fn($q) => $q->orderByDesc('created_at'))
            ->paginate($limit, ['*'], 'page', (int)($offset / $limit) + 1);
    }

    /**
     * List produk untuk Management (Admin).
     */
    public function listAdmin(array $params): LengthAwarePaginator
    {
        $limit = (int) ($params['limit'] ?? 10);
        $offset = (int) ($params['offset'] ?? 0);

        return Product::query()
            ->with(['category', 'brand'])
            ->when(!empty($params['brand_id']), fn($q) => $q->where('brand_id', $params['brand_id']))
            ->when(!empty($params['category_id']), fn($q) => $q->where('category_id', $params['category_id']))
            ->when(!empty($params['search']), function ($q) use ($params) {
                $search = $params['search'];
                $q->where(
                    fn($sub) =>
                    $sub->where('name', 'ilike', "%{$search}%")
                        ->orWhere('sku', 'ilike', "%{$search}%")
                );
            })
            ->when($params['sort_col'] ?? null, function ($q, $col) use ($params) {
                $dir = strtolower($params['sort_dir'] ?? 'desc') === 'asc' ? 'asc' : 'desc';
                // Handle sorting by related brand name if needed
                if ($col === 'brand_name') {
                    $q->join('brands', 'products.brand_id', '=', 'brands.id')
                        ->orderBy('brands.name', $dir)
                        ->select('products.*');
                } else {
                    $q->orderBy($col, $dir);
                }
            }, fn($q) => $q->orderByDesc('created_at'))
            ->paginate($limit, ['*'], 'page', (int)($offset / $limit) + 1);
    }

    public function getById(string $id): ?Product
    {
        return Product::with(['category', 'brand'])->find($id);
    }

    public function getBySlug(string $slug): ?Product
    {
        return Product::with(['category', 'brand'])->where('slug', $slug)->first();
    }

    public function update(array $params): ?Product
    {
        $product = Product::find($params['id']);
        if ($product) {
            $product->update($params);
        }
        return $product;
    }

    public function delete(string $id): void
    {
        Product::destroy($id);
    }

    public function restore(string $id): ?Product
    {
        $product = Product::withTrashed()->find($id);
        if ($product) {
            $product->restore();
        }
        return $product;
    }
}
