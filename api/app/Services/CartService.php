<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;

class CartService
{
    public function resolveCart(?User $user, ?string $guestToken): Cart
    {
        if ($user !== null) {
            return Cart::firstOrCreate([
                'user_id' => $user->id
            ]);
        }

        if ($guestToken !== null) {
            $cart = Cart::where('guest_token', $guestToken)->first();

            if ($cart !== null) {
                return $cart;
            }
        }

        return Cart::create([
            'guest_token' => (string) Str::uuid(),
        ]);
    }

    public function findCart(?User $user, ?string $guestToken): Cart
    {
        if ($user !== null) {
            $cart = Cart::where('user_id', $user->id)->first();
        } elseif ($guestToken !== null) {
            $cart = Cart::where('guest_token', $guestToken)->first();
        } else {
            throw ValidationException::withMessages(['cart' => 'Identificação do carrinho não informada.']);
        }
        if ($cart === null) {
            throw ValidationException::withMessages(['cart' => 'Carrinho não encontrado.']);
        }

        return $cart;
    }

    public function findGuestCart(string $guestToken): ?Cart
    {
        return Cart::where('guest_token', $guestToken)->first();
    }

    public function addItem(Cart $cart, Product $product, int $quantity = 1): CartItem
    {
        return  DB::transaction(function () use ($cart, $product, $quantity) {
            if ($quantity < 1) {
                throw ValidationException::withMessages(['quantity' => 'A quantidade mínima é 1.']);
            }

            $product = Product::where('id', $product->id)->lockForUpdate()->firstOrFail();

            if (!$product->active || $product->stock < 1) {
                throw ValidationException::withMessages([
                    'product' => 'Produto indisponível ou sem estoque.',
                ]);
            }

            $item = $cart->items()->where('product_id', $product->id)->first();

            $currentQuantity = $item?->quantity ?? 0;
            $newQuantity = $currentQuantity + $quantity;

            if ($newQuantity > $product->stock) {
                $availableToAdd = max(0, $product->stock - $currentQuantity);

                throw ValidationException::withMessages([
                    'quantity' => [
                        "Estoque insuficiente. Você ainda pode adicionar no máximo {$availableToAdd} unidade(s).",
                    ],
                ]);
            }

            return $cart->items()->updateOrCreate(
                ['product_id' => $product->id],
                ['quantity' => $newQuantity],
            );
        });
    }

    public function updateItemQuantity(Cart $cart, int $productId, int $quantity): void
    {
        DB::transaction(function () use ($cart, $productId, $quantity) {
            if ($productId < 1) {
                throw ValidationException::withMessages(['product' => 'ID do produto inválido.']);
            }

            if ($quantity === 0) {
                $cart->items()
                ->where('product_id', $productId)
                ->delete();

                return;
            } elseif ($quantity < 0) {
                throw ValidationException::withMessages(['product' => 'Valor informado não aceito.']);
            }

            $product = Product::where('id', $productId)->lockForUpdate()->firstOrFail();

            $item = $cart->items()->where('product_id', $productId)->first();

            if (!$item) {
                throw ValidationException::withMessages(['product' => 'Produto não encontrado no carrinho.',]);
            }

            if (!$product->active || $product->stock < $quantity) {
                throw ValidationException::withMessages(['quantity' => 'Estoque insuficiente ou produto indisponível.']);
            }

            $item->update([
                'quantity' => $quantity,
            ]);
        });
    }

    public function mergeGuestCart(User $user, string $guestToken): array
    {
        return DB::transaction(function () use ($user, $guestToken) {
            $warnings = [];

            $guestCart = Cart::where('guest_token', $guestToken)->with('items')->first();

            if (!$guestCart) {
                return ['products' => []];
            }

            $cartUser = Cart::firstOrCreate(['user_id' => $user->id]);

            foreach ($guestCart->items as $item) {
                $product = Product::where('id', $item->product_id)->lockForUpdate()->firstOrFail();

                if ($product->stock < 1 || !$product->active) {
                    $warnings['products'][] = [
                        'product_id' => $item->product_id,
                        'message' => $product->name .': Produto indisponível ou fora de estoque.'
                        ];
                    continue;
                }

                $currentQuantity = $cartUser->items()->where('product_id', $item->product_id)->first()?->quantity ?? 0;
                $newQuantity = $currentQuantity + $item->quantity;

                $finalQuantity = min($newQuantity, $product->stock);

                $cartUser->items()->updateOrCreate(
                    ['product_id' => $item->product_id],
                    ['quantity' => $finalQuantity]
                );

                if ($newQuantity > $product->stock) {
                    $warnings['products'][] = [
                        'product_id' => $item->product_id,
                        'message' => $product->name . ': estoque insuficiente para a quantidade desejada. Quantidade ajustada.'
                    ];
                }

            }
            $guestCart->delete();

            return ['products' => $warnings['products'] ?? []];
        });
    }

    public function removeItem(Cart $cart, int $productId): void
    {
        if ($productId < 1) {
            throw ValidationException::withMessages(['product' => 'ID do produto inválido.']);
        }

        $item = $cart->items()->where('product_id', $productId)->first();

        if (!$item) {
            throw ValidationException::withMessages(['product' => 'Produto não encontrado no carrinho.',]);
        }

        $item->delete();
    }

    public function getCart(Cart $cart): array
    {
        $items = $cart->items()
            ->with('product')
            ->get();

        $totalCents = 0;

        $items = $items->map(function ($item) use (&$totalCents) {
            $unitPriceCents = (int) str_replace('.', '', $item->product->price);

            $subtotalCents = $unitPriceCents * $item->quantity;

            $totalCents += $subtotalCents;

            return [
                'product_id' => $item->product_id,
                'name' => $item->product->name,
                'image_url' => $this->imageUrl($item->product->image_url),
                'stock' => $item->product->stock,
                'unit_price' => number_format($unitPriceCents / 100, 2, '.', ''),
                'quantity' => $item->quantity,
                'subtotal' => number_format($subtotalCents / 100, 2, '.', ''),
                'available' => $item->product->active && $item->product->stock >= $item->quantity,
            ];
        });

        return [
            'items' => $items,
            'total' => number_format($totalCents / 100, 2, '.', ''),
        ];
    }

    private function imageUrl(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        $baseUrl = rtrim((string) config('filesystems.disks.public.url', url('/storage')), '/');

        return $baseUrl.'/'.ltrim($path, '/');
    }
}
