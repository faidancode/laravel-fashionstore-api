<?php

namespace App\Http\Controllers\Api\v1;

use App\DTOs\Cart\AddCartItemRequest;
use App\DTOs\Cart\UpdateCartItemRequest;
use App\Enums\GlobalErrorCode;
use App\Http\Controllers\Controller;
use App\Services\CartService;
use App\Traits\ApiResponse;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CartController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected CartService $cartService
    ) {
    }

    public function show(Request $request): JsonResponse
    {
        try {
            $items = $this->cartService->getCartDetail($request->user()->id);
            return $this->successResponse($items, 'Detail cart berhasil diambil');
        } catch (Exception $e) {
            return $this->errorFromEnum(GlobalErrorCode::INTERNAL_ERROR);
        }
    }

    public function store(AddCartItemRequest $request): JsonResponse
    {
        try {
            $data = $request->toDto();
            $items = $this->cartService->addItem(
                $request->user()->id,
                $data['product_id'],
                $data['quantity']
            );

            return $this->successResponse($items, 'Item berhasil ditambahkan ke cart', 201);
        } catch (Exception $e) {
            return $this->errorFromEnum(GlobalErrorCode::INTERNAL_ERROR);
        }
    }

    public function update(UpdateCartItemRequest $request, string $productId): JsonResponse
    {
        if (!Str::isUuid($productId)) {
            return $this->errorFromEnum(GlobalErrorCode::INVALID_UUID);
        }

        try {
            $items = $this->cartService->updateQty(
                $request->user()->id,
                $productId,
                $request->validated()['quantity']
            );

            return $this->successResponse($items, 'Quantity item berhasil diperbarui');
        } catch (ModelNotFoundException $e) {
            return $this->errorFromEnum(GlobalErrorCode::NOT_FOUND);
        } catch (Exception $e) {
            return $this->errorFromEnum(GlobalErrorCode::INTERNAL_ERROR);
        }
    }

    public function increment(Request $request, string $productId): JsonResponse
    {
        if (!Str::isUuid($productId)) {
            return $this->errorFromEnum(GlobalErrorCode::INVALID_UUID);
        }

        try {
            $items = $this->cartService->incrementQty($request->user()->id, $productId);
            return $this->successResponse($items, 'Quantity item berhasil ditambahkan');
        } catch (ModelNotFoundException $e) {
            return $this->errorFromEnum(GlobalErrorCode::NOT_FOUND);
        } catch (Exception $e) {
            return $this->errorFromEnum(GlobalErrorCode::INTERNAL_ERROR);
        }
    }

    public function decrement(Request $request, string $productId): JsonResponse
    {
        if (!Str::isUuid($productId)) {
            return $this->errorFromEnum(GlobalErrorCode::INVALID_UUID);
        }

        try {
            $items = $this->cartService->decrementQty($request->user()->id, $productId);
            return $this->successResponse($items, 'Quantity item berhasil dikurangi');
        } catch (ModelNotFoundException $e) {
            return $this->errorFromEnum(GlobalErrorCode::NOT_FOUND);
        } catch (Exception $e) {
            return $this->errorFromEnum(GlobalErrorCode::INTERNAL_ERROR);
        }
    }

    public function destroy(Request $request, string $productId): JsonResponse
    {
        if (!Str::isUuid($productId)) {
            return $this->errorFromEnum(GlobalErrorCode::INVALID_UUID);
        }

        try {
            $items = $this->cartService->deleteItem($request->user()->id, $productId);
            return $this->successResponse($items, 'Item berhasil dihapus dari cart');
        } catch (ModelNotFoundException $e) {
            return $this->errorFromEnum(GlobalErrorCode::NOT_FOUND);
        } catch (Exception $e) {
            return $this->errorFromEnum(GlobalErrorCode::INTERNAL_ERROR);
        }
    }

    public function clear(Request $request): JsonResponse
    {
        try {
            $items = $this->cartService->clearCart($request->user()->id);
            return $this->successResponse($items, 'Cart berhasil dikosongkan');
        } catch (Exception $e) {
            return $this->errorFromEnum(GlobalErrorCode::INTERNAL_ERROR);
        }
    }
}
