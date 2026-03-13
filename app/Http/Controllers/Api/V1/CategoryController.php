<?php

namespace App\Http\Controllers\Api\v1;

use App\DTOs\Category\StoreCategoryRequest;
use App\Enums\GlobalErrorCode;
use App\Http\Controllers\Controller;
use App\Services\CategoryService;
use App\Traits\ApiResponse;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected CategoryService $categoryService
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $params = [
            'search' => $request->query('search'),
            'limit' => $request->query('limit', 10),
            'sort_by' => $request->query('sort_by', 'created_at'),
            'sort_order' => $request->query('sort_order', 'desc'),
        ];

        $categories = $this->categoryService->getCategories($params);

        return $this->paginatedResponse($categories, 'Daftar kategori berhasil diambil');
    }

    public function store(StoreCategoryRequest $request): JsonResponse
    {
        try {
            $category = $this->categoryService->createCategory($request->toDto());

            return $this->successResponse($category, 'Kategori berhasil ditambahkan', 201);
        } catch (Exception $e) {
            return $this->errorFromEnum(GlobalErrorCode::INTERNAL_ERROR);
        }
    }

    public function show(string $id): JsonResponse
    {
        if (!Str::isUuid($id)) {
            return $this->errorFromEnum(GlobalErrorCode::INVALID_UUID);
        }

        try {
            $category = $this->categoryService->getCategoryDetail($id);
            return $this->successResponse($category, 'Kategori ditemukan');
        } catch (ModelNotFoundException $e) {
            return $this->errorFromEnum(GlobalErrorCode::NOT_FOUND);
        } catch (Exception $e) {
            return $this->errorFromEnum(GlobalErrorCode::INTERNAL_ERROR);
        }
    }

    public function update(StoreCategoryRequest $request, string $id): JsonResponse
    {
        if (!Str::isUuid($id)) {
            return $this->errorFromEnum(GlobalErrorCode::INVALID_UUID);
        }

        try {
            $category = $this->categoryService->updateCategory($id, $request->validated());
            return $this->successResponse($category, 'Kategori berhasil diperbarui');
        } catch (ModelNotFoundException $e) {
            return $this->errorFromEnum(GlobalErrorCode::NOT_FOUND);
        } catch (Exception $e) {
            return $this->errorFromEnum(GlobalErrorCode::INTERNAL_ERROR);
        }
    }

    public function destroy(string $id): JsonResponse
    {
        if (!Str::isUuid($id)) {
            return $this->errorFromEnum(GlobalErrorCode::INVALID_UUID);
        }

        try {
            $this->categoryService->deleteCategory($id);
            return $this->successResponse(null, 'Kategori berhasil dihapus');
        } catch (ModelNotFoundException $e) {
            return $this->errorFromEnum(GlobalErrorCode::NOT_FOUND);
        } catch (Exception $e) {
            return $this->errorFromEnum(GlobalErrorCode::INTERNAL_ERROR);
        }
    }
}
