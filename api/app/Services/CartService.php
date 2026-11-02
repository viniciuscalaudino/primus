<?php

namespace App\Services;


use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CartService
{
    public function resolveCart(?User $user, ?string $guestToken): Cart
    {
        if ($user !== null){
            return Cart::firstOrCreate([
                'user_id' => $user->id
            ]);
        }

        if($guestToken !== null){
            $cart = Cart::where('guest_token', $guestToken)->first();
            if($cart !== null){
                return $cart;
            }  
        }

        return Cart::create([
            'guest_token' => (string) Str::uuid(),
        ]);
    }

    public function addItem (Cart $cart, Product $product, int $quantity = 1): CartItem
    {
        if($quantity < 1){
            throw ValidationException::withMessages(['quantity' => 'A quantidade mínima é 1.']);
        }

        if(!$product->active || $product->stock < 1 ){
            throw ValidationException::withMessages(['product' => 'Produto indisponível.']);
        }

        $item = $cart->items()->where('product_id', $product->id)->first();
        
        $currentQuantity = $item?->quantity ?? 0;
        $newQuantity = $currentQuantity + $quantity;
      
        if($newQuantity > $product->stock){
            throw ValidationException::withMessages(['quantity' => 'Estoque insuficiente.',]);
        }

        return $cart->items()->updateOrCreate(
            ['product_id' => $product->id],
            ['quantity' => $newQuantity],
        );
    }
}