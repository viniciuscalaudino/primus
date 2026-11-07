<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(private AuthService $authService){}

    public function register(RegisterRequest $request)
    {
        $result = $this->authService->register(
            $request->validated(),
            $request
        );

        return (new UserResource($result['user']))
            ->additional(['cart_warnings' => $result['cart_warnings']])
            ->response()
            ->setStatusCode(201)
            ->header('Cache-Control', 'no-store');
    }

    public function login(LoginRequest $request)
    {
        $result = $this->authService->login($request->validated(), $request);

        return (new UserResource($result['user']))
            ->additional(['cart_warnings' => $result['cart_warnings']])
            ->response()
            ->header('Cache-Control', 'no-store');
    }

    public function me(Request $request)
    {
        return new UserResource($request->user());
    }

    public function logout(Request $request)
    {
        
        $this->authService->logout($request);

        return response()->noContent();
    }
}

