<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAddressRequest;
use App\Services\AddressService;
use App\Http\Resources\AddressResource;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    public function __construct(private AddressService $addressService) {}

    public function index(Request $request)
    {
        $addresses = $this->addressService->getAddresses($request->user());

        return AddressResource::collection($addresses);
    }

    public function store(StoreAddressRequest $request)
    {
        $address = $this->addressService->createAddress(
            $request->user(),
            $request->validated()
        );

        return (new AddressResource($address))
            ->response()
            ->setStatusCode(201);
    }

    public function setDefault(Request $request, int $addressId)
    {
        $address = $this->addressService->setDefaultAddress($request->user(), $addressId);

        return new AddressResource($address);
    }
}
