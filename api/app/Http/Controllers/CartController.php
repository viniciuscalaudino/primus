<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\CartService;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct(private CartService $cartService){}

    public function show(Request $request)
    {
        $user =  $request->user();

        $guestToken = $request->header('X-Cart-Token');

        $cart = $this->cartService->resolveCart(
            $user,
            $guestToken
        );

        $cartData = $this->cartService->getCart($cart);

        return response()->json([
            'cart_token' => $cart->guest_token,
            ...$cartData
        ]);
    }
}
