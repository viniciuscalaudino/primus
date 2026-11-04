<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(private AuthService $authService){}

    public function register(RegisterRequest $request)
    {
        $user = $this->authService->register(
            $request->validate(),
            $request,
        );

        return (new UserResource($user))
            ->response()
            ->setStatusCode(201)
            ->header('Cache-Control', 'no-store');
    }

    public function login(LoginRequest $request)
    {
        $result = $this->authService->login($request->validated());

        return response()->json($result, 200);
    }

    public function logout(Request $request)
    {
        
        $this->authService->logout($request->user());

        return response()->json(['message' => 'Logout realizado.']);
    }
}

