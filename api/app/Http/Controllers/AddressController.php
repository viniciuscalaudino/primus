<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAddressRequest;
use App\Services\AddressService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    public function __construct(private AddressService $addressService) {}

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $addresses = $this->addressService->getAddresses($user);

        return response()->json($addresses);
    }

    public function store(StoreAddressRequest $request): JsonResponse
    {
        $address = $this->addressService->createAddress(
            $request->user(),
            $request->validated()
        );

        return response()->json($address, 201);
    }

    public function setDefault(Request $request, int $addressId): JsonResponse
    {
        $user = $request->user();

        $address = $this->addressService->setDefaultAddress($user, $addressId);

        return response()->json($address);
    }
}
