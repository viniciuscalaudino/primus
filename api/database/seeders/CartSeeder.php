<?php

namespace Database\Seeders;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class CartSeeder extends Seeder
{
    public const GUEST_TOKEN = '11111111-1111-4111-8111-111111111111';


    public function run(): void
    {
        $guestCart = Cart::firstOrCreate([
            'guest_token' => self::GUEST_TOKEN,
        ]);

        $this->addItem($guestCart, 'Whey Protein Concentrado 900g', 1);
        $this->addItem($guestCart, 'Energy Gel 30g', 2);

        $user = User::where('email', 'cliente3@primus.com')->firstOrFail();

        $userCart = Cart::firstOrCreate([
            'user_id' => $user->id,
        ]);

        $this->addItem($userCart, 'Creatina Monohidratada 300g', 2);
        $this->addItem($userCart, 'Coqueteleira 600ml', 1);
    }

    private function addItem(Cart $cart, string $productName, int $quantity): void
    {
        $product = Product::where('name', $productName)->firstOrFail();

        CartItem::updateOrCreate(
            [
                'cart_id' => $cart->id,
                'product_id' => $product->id,
            ],
            [
                'quantity' => $quantity,
            ]
        );
    }

}