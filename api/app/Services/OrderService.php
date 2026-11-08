<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\Cart;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderService
{
    public function createOrder(User $user, int $addressId): Order
    {
        return DB::transaction(function () use ($user, $addressId){
            $cart = $user->cart()->with('items')->first();
            
            if(!$cart || $cart->items->isEmpty()){
                throw ValidationException::withMessages([
                'cart' => ['Carrinho vazio.']
                ]);
            }

            $address = $user->addresses()
                ->where('id', $addressId)
                ->firstOrFail();

            $productIds = $cart->items
                ->pluck('product_id')
                ->unique()
                ->sort()
                ->values();

            $lockedProducts = Product::whereIn('id', $productIds)
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

           foreach($cart->items as $item) {
                $product = $lockedProducts->get($item->product_id);

                if (!$product) {
                    throw ValidationException::withMessages([
                        'product' => ['Produto não encontrado.']
                    ]);
                }

                
                if (!$product->active || $product->stock < $item->quantity) {
                    throw ValidationException::withMessages([
                        'product' => ["Estoque insuficiente ou produto inativo: {$product->name}."]
                    ]);
                }   
            }
        });
    }
}