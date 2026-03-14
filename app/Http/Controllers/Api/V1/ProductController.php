<?php

namespace App\Http\Controllers\Api\v1;

use App\DTOs\Product\StoreProductRequest;
use App\Enums\GlobalErrorCode;
use App\Http\Controllers\Controller;
use App\Services\ProductService;
use App\Traits\ApiResponse;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected ProductService $productService
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $params = [
            'search' => $request->query('search'),
            'limit' => $request->query('limit', 10),
            'sort_by' => $request->query('sort_by', 'created_at'),
            'sort_order' => $request->query('sort_order', 'desc'),
            'category_id' => $request->query('category_id'),
            'brand_id' => $request->query('brand_id'),
        ];

        $products = $this->productService->getProducts($params);

        return $this->paginatedResponse($products, 'Daftar produk berhasil diambil');
    }

    public function store(StoreProductRequest $request): JsonResponse
    {
        try {
            $product = $this->productService->createProduct($request->toDto());

            return $this->successResponse($product, 'Produk berhasil ditambahkan', 201);
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
            $product = $this->productService->getProductDetail($id);
            return $this->successResponse($product, 'Produk ditemukan');
        } catch (ModelNotFoundException $e) {
            return $this->errorFromEnum(GlobalErrorCode::NOT_FOUND);
        } catch (Exception $e) {
            return $this->errorFromEnum(GlobalErrorCode::INTERNAL_ERROR);
        }
    }

    public function update(StoreProductRequest $request, string $id): JsonResponse
    {
        if (!Str::isUuid($id)) {
            return $this->errorFromEnum(GlobalErrorCode::INVALID_UUID);
        }

        try {
            $product = $this->productService->updateProduct($id, $request->validated());
            return $this->successResponse($product, 'Produk berhasil diperbarui');
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
            $this->productService->deleteProduct($id);
            return $this->successResponse(null, 'Produk berhasil dihapus');
        } catch (ModelNotFoundException $e) {
            return $this->errorFromEnum(GlobalErrorCode::NOT_FOUND);
        } catch (Exception $e) {
            return $this->errorFromEnum(GlobalErrorCode::INTERNAL_ERROR);
        }
    }
}
