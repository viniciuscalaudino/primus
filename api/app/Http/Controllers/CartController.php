<?php

namespace App\Http\Controllers;

use App\Services\CartService;
use Illuminate\Http\Request;
use App\Models\Product;

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

    public function store (Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $user = $request->user();

        $guestToken = $request->header('X-Cart-Token');

        $cart = $this->cartService->resolveCart($user, $guestToken);

        $product = Product::findOrFail($validated['product_id']);

        $this->cartService->addItem($cart, $product, $validated['quantity']);
        
        return response()->json([
            'message' => 'Produto adicionado ao carrinho com sucesso.',
            'cart_token' => $cart->guest_token,
            'cart' => $this->cartService->getCart($cart)
        ], 201);
    }

    public function update(Request $request, int $productId)
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:0',
        ]);

        $user = $request->user();

        $guestToken = $request->header('X-Cart-Token');

        $cart = $this->cartService->findCart($user, $guestToken);

        $this->cartService->updateItemQuantity($cart, $productId, $validated['quantity']);

        return response()->json([
            'cart' => $this->cartService->getCart($cart)
        ], 200);
    }

    public function destroy(Request $request, int $productId)
    {
        $user = $request->user();

        $guestToken = $request->header('X-Cart-Token');

        $cart = $this->cartService->findCart($user, $guestToken);

        $this->cartService->removeItem($cart, $productId);

        return response()->json([
            'cart' => $this->cartService->getCart($cart)
        ], 200);
    }
}