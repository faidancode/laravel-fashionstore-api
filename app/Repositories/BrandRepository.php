<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Brand;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class BrandRepository
{
    /**
     * Membuat brand baru.
     */
    public function create(array $params): Brand
    {
        return Brand::create($params);
    }

    /**
     * Daftar brand untuk publik (hanya yang aktif).
     */
    public function listPublic(int $limit = 10, int $offset = 0): LengthAwarePaginator
    {
        return Brand::query()
            ->where('is_active', true)
            ->orderByDesc('created_at')
            ->paginate($limit, ['*'], 'page', (int)($offset / $limit) + 1);
    }

    /**
     * Daftar brand untuk admin dengan fitur search dan sorting.
     */
    public function listAdmin(array $params): LengthAwarePaginator
    {
        $limit = (int) ($params['limit'] ?? 10);
        $offset = (int) ($params['offset'] ?? 0);

        return Brand::query()
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
     * Daftar brand dengan pagination sederhana.
     */
    public function getPaginated(array $params): LengthAwarePaginator
    {
        $query = Brand::query();

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

    public function getById(string $id): ?Brand
    {
        return Brand::find($id);
    }

    public function getBySlug(string $slug): ?Brand
    {
        return Brand::where('slug', $slug)->first();
    }

    /**
     * Update data brand.
     */
    public function update(string $id, array $params): ?Brand
    {
        $brand = Brand::find($id);

        if ($brand) {
            $brand->update($params);
        }

        return $brand;
    }

    /**
     * Hapus brand (Soft Delete).
     */
    public function delete(string $id): bool
    {
        return (bool) Brand::where('id', $id)->delete();
    }

    /**
     * Restore brand yang telah dihapus.
     */
    public function restore(string $id): ?Brand
    {
        $brand = Brand::withTrashed()->find($id);

        if ($brand) {
            $brand->restore();
        }

        return $brand;
    }
}
