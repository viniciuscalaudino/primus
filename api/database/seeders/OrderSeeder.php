<?php

namespace Database\Seeders;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\AddressService;
use App\Services\OrderService;
use Illuminate\Database\Seeder;


class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $orders = app(OrderService::class);
        $addresses = app(AddressService::class);

        $cliente1 = User::where('email', 'cliente1@primus.com')->firstOrFail();
        $cliente2 = User::where('email', 'cliente2@primus.com')->firstOrFail();
        $cliente3 = User::where('email', 'cliente3@primus.com')->firstOrFail();
        
        
        $address1 = $addresses->createAddress($cliente1, [
            'recipient_name' => 'Cliente 1',
            'zip_code' => '01310-100',
            'street' => 'Avenida Paulista',
            'number' => '1000',
            'complement' => 'Apto 12',
            'neighborhood' => 'Bela Vista',
            'city' => 'São Paulo',
            'state' => 'SP',
        ]);

        $address2 = $addresses->createAddress($cliente2, [
            'recipient_name' => 'Cliente 2',
            'zip_code' => '20040-020',
            'street' => 'Rua da Assembleia',
            'number' => '50',
            'complement' => null,
            'neighborhood' => 'Centro',
            'city' => 'Rio de Janeiro',
            'state' => 'RJ',
        ]);

        $addresses->createAddress($cliente3, [
            'recipient_name' => 'Cliente 3',
            'zip_code' => '30130-010',
            'street' => 'Avenida Afonso Pena',
            'number' => '200',
            'complement' => null,
            'neighborhood' => 'Centro',
            'city' => 'Belo Horizonte',
            'state' => 'MG',
        ]);

        $this->placeOrder($orders, $cliente1, $address1->id, [
            'Whey Protein Concentrado 900g' => 1,
            'Coqueteleira 600ml' => 1,
        ]);

        $paid = $this->placeOrder($orders, $cliente1, $address1->id, [
            'Whey Protein Isolado 900g' => 2,
        ]);
        $orders->payOrder($cliente1, $paid->id);

        $shipped = $this->placeOrder($orders, $cliente2, $address2->id, [
            'Creatina Monohidratada 300g' => 1,
            'Energy Gel 30g' => 3,
        ]);
        $orders->payOrder($cliente2, $shipped->id);
        $orders->updateOrderStatus($shipped->id, Order::STATUS_SHIPPED);

        $delivered = $this->placeOrder($orders, $cliente2, $address2->id, [
            'Cinto de Musculação' => 1,
            'Caseína 900g' => 1,
        ]);
        $orders->payOrder($cliente2, $delivered->id);
        $orders->updateOrderStatus($delivered->id, Order::STATUS_SHIPPED);
        $orders->updateOrderStatus($delivered->id, Order::STATUS_DELIVERED);

        $cancelled = $this->placeOrder($orders, $cliente2, $address2->id, [
            'Whey Protein Concentrado 900g' => 2,
        ]);
        $orders->cancelOrder($cliente2, $cancelled->id);
    }

    private function placeOrder(OrderService $orders, User $user, int $addressId, array $items): Order
    {
        $cart = Cart::firstOrCreate(['user_id' => $user->id]);

        foreach ($items as $productName => $quantity) {
            $product = Product::where('name', $productName)->firstOrFail();

            CartItem::create([
                'cart_id' => $cart->id,
                'product_id' => $product->id,
                'quantity' => $quantity,
            ]);
        }

        return $orders->createOrder($user, $addressId);
    }
}

