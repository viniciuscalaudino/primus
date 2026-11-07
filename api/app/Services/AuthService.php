<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;

class AuthService 
{
    public function __construct(private CartService $cartService) {}

    public function register (array $data, Request $request): array
    {
        $user = User::create($data);

        $user->refresh();

        Auth::guard('web')->login($user);
        $request->session()->regenerate();

        $guestToken = $request->header('X-Cart-Token');

        $warnings = $this->mergeCartIfPresent($user, $guestToken);

        return ['user' => $user, 'cart_warnings' => $warnings];
    }

    private function mergeCartIfPresent(User $user,  ?string $guestToken): array
    {
        if(!$guestToken){
            return ['products' => []];
        }

        return $this->cartService->mergeGuestCart($user, $guestToken);
    }

    public function login (array $credentials, Request $request): array
    {
        if (! Auth::guard('web')->attempt($credentials)) {
            throw ValidationException::withMessages(['email' => 'E-mail ou senha incorretos.',]);
        }

        $request->session()->regenerate();

        $user = Auth::guard('web')->user();

        $guestToken = $request->header('X-Cart-Token');

        $warnings = $this->mergeCartIfPresent($user, $guestToken);

        return ['user' => $user, 'cart_warnings' => $warnings];
    }

    public function logout (Request $request): void
    {
       Auth::guard('web')->logout();

       $request->session()->invalidate();
       $request->session()->regenerateToken();
    }
}