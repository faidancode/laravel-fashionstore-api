<?php

namespace App\Http\Controllers\Api\v1;

use App\DTOs\Brand\StoreBrandRequest;
use App\Enums\GlobalErrorCode;
use App\Http\Controllers\Controller;
use App\Services\BrandService;
use App\Traits\ApiResponse;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BrandController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected BrandService $brandService
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

        $brands = $this->brandService->getBrands($params);

        return $this->paginatedResponse($brands, 'Daftar brand berhasil diambil');
    }

    public function store(StoreBrandRequest $request): JsonResponse
    {
        try {
            $brand = $this->brandService->createBrand($request->toDto());

            return $this->successResponse($brand, 'Brand berhasil ditambahkan', 201);
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
            $brand = $this->brandService->getBrandDetail($id);
            return $this->successResponse($brand, 'Brand ditemukan');
        } catch (ModelNotFoundException $e) {
            return $this->errorFromEnum(GlobalErrorCode::NOT_FOUND);
        } catch (Exception $e) {
            return $this->errorFromEnum(GlobalErrorCode::INTERNAL_ERROR);
        }
    }

    public function update(StoreBrandRequest $request, string $id): JsonResponse
    {
        if (!Str::isUuid($id)) {
            return $this->errorFromEnum(GlobalErrorCode::INVALID_UUID);
        }

        try {
            $brand = $this->brandService->updateBrand($id, $request->validated());
            return $this->successResponse($brand, 'Brand berhasil diperbarui');
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
            $this->brandService->deleteBrand($id);
            return $this->successResponse(null, 'Brand berhasil dihapus');
        } catch (ModelNotFoundException $e) {
            return $this->errorFromEnum(GlobalErrorCode::NOT_FOUND);
        } catch (Exception $e) {
            return $this->errorFromEnum(GlobalErrorCode::INTERNAL_ERROR);
        }
    }
}
