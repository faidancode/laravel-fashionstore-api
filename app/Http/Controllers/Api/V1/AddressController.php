<?php

namespace App\Http\Controllers\Api\v1;

use App\DTOs\Address\StoreAddressRequest as AddressStoreAddressRequest;
use App\Enums\GlobalErrorCode;
use App\Http\Controllers\Controller;
use App\Services\AddressService;
use App\Traits\ApiResponse;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AddressController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected AddressService $addressService
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

        $addresses = $this->addressService->getUserAddresses($request->user()->id, $params);

        return $this->paginatedResponse($addresses, 'Daftar alamat berhasil diambil');
    }

    public function store(AddressStoreAddressRequest $request): JsonResponse
    {
        try {
            $address = $this->addressService->createAddress($request->toDto());

            // Status 201 Created untuk resource baru
            return $this->successResponse($address, 'Alamat berhasil ditambahkan', 201);
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
            $address = $this->addressService->getAddressDetail($id);
            return $this->successResponse($address, 'Alamat ditemukan');
        } catch (ModelNotFoundException $e) {
            return $this->errorFromEnum(GlobalErrorCode::NOT_FOUND);
        } catch (Exception $e) {
            return $this->errorFromEnum(GlobalErrorCode::INTERNAL_ERROR);
        }
    }

    public function update(AddressStoreAddressRequest $request, string $id): JsonResponse
    {
        if (!Str::isUuid($id)) {
            return $this->errorFromEnum(GlobalErrorCode::INVALID_UUID);
        }

        try {
            $address = $this->addressService->updateAddress($id, $request->validated());
            return $this->successResponse($address, 'Alamat berhasil diperbarui');
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
            $this->addressService->deleteAddress($id);
            // Untuk delete, biasanya data diisi null
            return $this->successResponse(null, 'Alamat berhasil dihapus');
        } catch (ModelNotFoundException $e) {
            return $this->errorFromEnum(GlobalErrorCode::NOT_FOUND);
        } catch (Exception $e) {
            return $this->errorFromEnum(GlobalErrorCode::INTERNAL_ERROR);
        }
    }

    public function setPrimary(Request $request, string $id): JsonResponse
    {
        if (!Str::isUuid($id)) {
            return $this->errorFromEnum(GlobalErrorCode::INVALID_UUID);
        }

        try {
            $this->addressService->setAsPrimary($request->user()->id, $id);
            return $this->successResponse(null, 'Alamat utama berhasil diperbarui');
        } catch (ModelNotFoundException $e) {
            return $this->errorFromEnum(GlobalErrorCode::NOT_FOUND);
        } catch (Exception $e) {
            return $this->errorFromEnum(GlobalErrorCode::INTERNAL_ERROR);
        }
    }
}