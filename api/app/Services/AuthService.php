<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class AuthService 
{
    public function __construct(private CartService $cartService) {}

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

        $user = Auth::guard('web')->user();

        $guestToken = $request->header('X-Cart-Token');

        if($guestToken === null){
            return $user;
        }

        $guestCart = $this->cartService->findGuestCart($guestToken);

        if($guestCart !== null){
            $this->cartService->mergeGuestCart($user, $guestToken);
        }

        return $user;
    }

    public function logout (Request $request): void
    {
       Auth::guard('web')->logout();

       $request->session()->invalidate();
       $request->session()->regenerateToken();
    }
}