<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;


class AuthService 
{
    public function register (array $data, Request $request): User
    {
        $user = User::create($data);

        Auth::guard('web')->login($user);
        $request->session()->regenerate();

        return $user;
    }

    public function login (array $credentials, Request $request): User
    {
        if (! Auth::guard('web')->attempt($credentials)) {
            throw ValidationException::withMessages([
                'email' => 'E-mail ou senha incorretos.',
            ]);
        }

        $request->session()->regenerate();

        return Auth::guard('web')->user();
    }

    public function logout (Request $request): void
    {
       Auth::guard('web')->logout();

       $request->session()->invalidate();
       $request->session()->regenerateToken();
    }
}