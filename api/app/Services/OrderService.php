<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderService
{
    private const SHIPPING_FIXED_CENTS = 1990;
    private const FREE_SHIPPING_THRESHOLD_CENTS = 29900;

    public function createOrder(User $user, int $addressId): Order
    {
        return DB::transaction(function () use ($user, $addressId){
            $cart = $user->cart()
                ->lockForUpdate()
                ->with('items')
                ->first();
            
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

            $subtotalCents = 0;

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
                
                $unitPriceCents = (int) str_replace('.', '', $product->price);

                $itemSubtotalCents = $unitPriceCents * $item->quantity;

                $subtotalCents += $itemSubtotalCents;  
            }

            $shippingCents = $subtotalCents >= self::FREE_SHIPPING_THRESHOLD_CENTS
                ? 0
                : self::SHIPPING_FIXED_CENTS;

            $totalCents = $subtotalCents + $shippingCents;

            $order = Order::create([
                'user_id' => $user->id,
                'subtotal' => number_format($subtotalCents / 100, 2, '.', ''),
                'shipping' => number_format($shippingCents / 100, 2, '.', ''),
                'total' => number_format($totalCents / 100, 2, '.', ''),
                'shipping_recipient_name' => $address->recipient_name,
                'shipping_zip_code' => $address->zip_code,
                'shipping_street' => $address->street,
                'shipping_number' => $address->number,
                'shipping_complement' => $address->complement,
                'shipping_neighborhood' => $address->neighborhood,
                'shipping_city' => $address->city,
                'shipping_state' => $address->state,
            ]);

            foreach($cart->items as $item) {
                $product = $lockedProducts->get($item->product_id);
    
                $unitPriceCents = (int) str_replace('.', '', $product->price);
    
                $itemSubtotalCents = $unitPriceCents * $item->quantity;
    
                $order->items()->create([
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'unit_price' => number_format($unitPriceCents / 100, 2, '.', ''),
                    'quantity' => $item->quantity,
                    'subtotal' => number_format($itemSubtotalCents / 100, 2, '.', ''),
                ]);
    
                $product->decrement('stock', $item->quantity);
            }

            $cart->items()->delete();

            return $order->refresh()->load('items');
        });
    }

    public function getUserOrders(User $user)
    {
        return $user->orders()
            ->with('items')
            ->latest()
            ->get();
    }

    public function getOrder(User $user, int $orderId): Order
    {
        if ($user->isAdmin()) {
            return Order::with('items')->findOrFail($orderId);
        }
    
        return $user->orders()
            ->with('items')
            ->findOrFail($orderId);   
    }

    public function payOrder(User $user, int $orderId): Order
    {
        return DB::transaction(function() use ($user, $orderId) {
            $order = Order::where('id', $orderId)
                ->lockForUpdate()
                ->firstOrFail();

            if ($order->user_id !== $user->id) {
                abort(403, 'Você não pode pagar este pedido.');
            }
            
            if($order->status !== Order::STATUS_AWAITING_PAYMENT) {
                abort(409, 'Este pedido não pode ser pago.');
            }

            $order->status = Order::STATUS_PAID;
            $order->paid_at = now();
            $order->save();

            return $order->load('items');
        });

    }

    public function cancelOrder(User $user, int $orderId): Order
    {
        return DB::transaction(function () use ($user, $orderId){
            $order = Order::where('id', $orderId)
                ->lockForUpdate()
                ->firstOrFail();

            if ($order->user_id !== $user->id && ! $user->isAdmin()) {
                abort(403, 'Você não pode cancelar este pedido.');
            }    

            if ($order->status !== Order::STATUS_AWAITING_PAYMENT) {
                abort(409, 'Este pedido não pode ser cancelado.');
            }

            $productIds = $order->items()
                ->pluck('product_id')
                ->unique()
                ->sort()
                ->values();

            $lockedProducts = Product::whereIn('id', $productIds)
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            foreach ($order->items as $item) {
                $product = $lockedProducts->get($item->product_id);

                $product->increment('stock', $item->quantity);   
            }

            $order->status = Order::STATUS_CANCELLED;
            $order->save();

            return $order->load('items');
        });
    }

    public function getAllOrders(?string $status = null)
    {
        $query = Order::with(['user', 'items']);

        if($status !== null) {
            $query->where('status', $status);
        }

        return $query->latest()->get();
    }

    public function updateOrderStatus(int $orderId, string $newStatus): Order
    {
        return DB::transaction(function () use ($orderId, $newStatus){ 
            $order = Order::where('id', $orderId)
                ->lockForUpdate()
                ->firstOrFail();
            
            $allowedTransitions = Order::TRANSITIONS[$order->status] ?? [];

            if (!in_array($newStatus, $allowedTransitions, true)) {
                abort(409, 'Transição de status não permitida.');
            }

            $order->status = $newStatus;
            $order->save();

            return $order->load('items');
        });
    }
}