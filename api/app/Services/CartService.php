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

    public function updateItemQuantity(Cart $cart, int $productId, int $quantity): void
    {
        if ($productId < 1) {
        throw ValidationException::withMessages(['product' => 'ID do produto inválido.']);
        }

        if($quantity === 0){
            $cart->items()
            ->where('product_id', $productId)
            ->delete();

            return;
        }
        elseif($quantity < 0){
            throw ValidationException::withMessages(['product' => 'Valor informado não aceito.']);   
        }
        
        $item = $cart->items()->where('product_id', $productId)->first();
    
        if(!$item){
             throw ValidationException::withMessages(['product' => 'Produto não encontrado no carrinho.',]);
        }

        $product = Product::find($productId);

        if (!$product) {
            throw ValidationException::withMessages(['product' => 'Produto não encontrado.']);
        }

        if (!$product->active || $product->stock < $quantity) {
            throw ValidationException::withMessages(['quantity' => 'Estoque insuficiente ou produto indisponível.']);
        }

        $item->update([
            'quantity' => $quantity,
        ]);

    }

    public function removeItem(Cart $cart, int $productId): void
    {
        $item = $cart->items()->where('product_id', $productId)->first();
    
        if(!$item){
             throw ValidationException::withMessages(['product' => 'Produto não encontrado no carrinho.',]);
        }

        $item->delete();
    }

    public function getCart(Cart $cart): array
    {
        $items = $cart->items()
            ->with('product')
            ->get();
        
        $items = $items->map(function($item) {
           return [
            'product_id' => $item->product_id,
            'name' => $item->product->name,
            'unit_price' => $item->product->price,
            'quantity' => $item->quantity,
            'subtotal' => $item->product->price * $item->quantity
           ];   
        });

        return [
            'items' => $items,
            'total' => $items->sum('subtotal'),
        ];
    }
}