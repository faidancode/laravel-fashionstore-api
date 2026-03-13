<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Category;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class CategoryRepository
{
    /**
     * Membuat kategori baru.
     */
    public function create(array $params): Category
    {
        return Category::create($params);
    }

    /**
     * Daftar kategori untuk publik (hanya yang aktif).
     */
    public function listPublic(int $limit = 10, int $offset = 0): LengthAwarePaginator
    {
        return Category::query()
            ->where('is_active', true)
            ->orderByDesc('created_at')
            ->paginate($limit, ['*'], 'page', (int)($offset / $limit) + 1);
    }

    /**
     * Daftar kategori untuk admin dengan fitur search dan sorting.
     */
    public function listAdmin(array $params): LengthAwarePaginator
    {
        $limit = (int) ($params['limit'] ?? 10);
        $offset = (int) ($params['offset'] ?? 0);

        return Category::query()
            ->when(!empty($params['search']), function ($query) use ($params) {
                $search = $params['search'];
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'ilike', "%{$search}%")
                        ->orWhere('description', 'ilike', "%{$search}%");
                });
            })
            ->when(isset($params['sort_col']), function ($query) use ($params) {
                $sortDir = strtolower($params['sort_dir'] ?? 'desc') === 'asc' ? 'asc' : 'desc';
                $query->orderBy($params['sort_col'], $sortDir);
            }, function ($query) {
                $query->orderByDesc('created_at');
            })
            ->paginate($limit, ['*'], 'page', (int)($offset / $limit) + 1);
    }

    /**
     * Daftar kategori dengan pagination sederhana.
     */
    public function getPaginated(array $params): LengthAwarePaginator
    {
        $query = Category::query();

        if (!empty($params['search'])) {
            $search = $params['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $sortBy = $params['sort_by'] ?? 'created_at';
        $sortOrder = strtolower($params['sort_order'] ?? 'desc') === 'asc' ? 'asc' : 'desc';

        return $query->orderBy($sortBy, $sortOrder)
            ->paginate($params['limit'] ?? 10);
    }

    public function getById(string $id): ?Category
    {
        return Category::find($id);
    }

    /**
     * Update data kategori.
     */
    public function update(string $id, array $params): ?Category
    {
        $category = Category::find($id);

        if ($category) {
            $category->update($params);
        }

        return $category;
    }

    /**
     * Hapus kategori (Soft Delete).
     */
    public function delete(string $id): bool
    {
        return (bool) Category::where('id', $id)->delete();
    }

    /**
     * Restore kategori yang telah dihapus.
     */
    public function restore(string $id): ?Category
    {
        $category = Category::withTrashed()->find($id);

        if ($category) {
            $category->restore();
        }

        return $category;
    }

    /**
     * Mengambil daftar ID berdasarkan array slug.
     * Berguna untuk filter produk berdasarkan kategori.
     */
    public function getIdsBySlugs(array $slugs): array
    {
        if (empty($slugs)) {
            return [];
        }

        return Category::whereIn('slug', $slugs)
            ->pluck('id')
            ->toArray();
    }
}
