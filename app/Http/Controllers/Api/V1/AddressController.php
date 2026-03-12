<?php

namespace App\Http\Controllers\Api\v1;

use App\DTOs\Address\StoreAddressRequest as AddressStoreAddressRequest;
use App\Http\Controllers\Controller;
use App\Http\Requests\Address\StoreAddressRequest;
use App\Services\AddressService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    public function __construct(
        protected AddressService $addressService
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $addresses = $this->addressService->getUserAddresses($request->user()->id);
        return response()->json(['data' => $addresses]);
    }

    public function store(AddressStoreAddressRequest $request): JsonResponse
    {
        $address = $this->addressService->createAddress($request->toDto());
        return response()->json(['message' => 'Alamat berhasil ditambahkan', 'data' => $address], 201);
    }

    public function show(string $id): JsonResponse
    {
        try {
            $address = $this->addressService->getAddressDetail($id);
            return response()->json(['data' => $address]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }

    public function update(AddressStoreAddressRequest $request, string $id): JsonResponse
    {
        $address = $this->addressService->updateAddress($id, $request->validated());
        return response()->json(['message' => 'Alamat berhasil diperbarui', 'data' => $address]);
    }

    public function destroy(string $id): JsonResponse
    {
        $this->addressService->deleteAddress($id);
        return response()->json(['message' => 'Alamat berhasil dihapus']);
    }

    public function setPrimary(Request $request, string $id): JsonResponse
    {
        $this->addressService->setAsPrimary($request->user()->id, $id);
        return response()->json(['message' => 'Alamat utama berhasil diperbarui']);
    }
}