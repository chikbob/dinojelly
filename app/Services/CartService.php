<?php

namespace App\Services;

use App\Http\Resources\CartItemResource;
use App\Models\CartItem;
use App\Models\User;
use Illuminate\Support\Collection;

class CartService
{
    public function __construct(
        protected AbandonedCartService $abandonedCartService,
    ) {
    }

    public function getCartItems(User $user): Collection
    {
        return CartItem::query()
            ->where('user_id', $user->id)
            ->with('product')
            ->get();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getCartMap(User $user): array
    {
        return $this->getCartItems($user)
            ->mapWithKeys(function (CartItem $item) {
                return [
                    $item->product_id => CartItemResource::make($item)->resolve(request()),
                ];
            })
            ->toArray();
    }

    public function getCartCount(User $user): int
    {
        return (int) CartItem::query()
            ->where('user_id', $user->id)
            ->sum('quantity');
    }

    public function addProduct(User $user, int $productId): void
    {
        $this->abandonedCartService->markRecovered($user, 'cart_updated');

        $cartItem = CartItem::query()->firstOrCreate(
            ['user_id' => $user->id, 'product_id' => $productId],
            ['quantity' => 0]
        );

        $cartItem->increment('quantity');
    }

    public function increaseQuantity(User $user, int $productId): void
    {
        $this->abandonedCartService->markRecovered($user, 'cart_updated');

        CartItem::query()
            ->where('user_id', $user->id)
            ->where('product_id', $productId)
            ->first()?->increment('quantity');
    }

    public function decreaseQuantity(User $user, int $productId): void
    {
        $this->abandonedCartService->markRecovered($user, 'cart_updated');

        $cartItem = CartItem::query()
            ->where('user_id', $user->id)
            ->where('product_id', $productId)
            ->first();

        if (!$cartItem) {
            return;
        }

        if ($cartItem->quantity > 1) {
            $cartItem->decrement('quantity');
            return;
        }

        $cartItem->delete();
    }

    public function removeProduct(User $user, int $productId): void
    {
        $this->abandonedCartService->markRecovered($user, 'cart_updated');

        CartItem::query()
            ->where('user_id', $user->id)
            ->where('product_id', $productId)
            ->delete();
    }

    public function clear(User $user): void
    {
        $this->abandonedCartService->markRecovered($user, 'cart_cleared');

        CartItem::query()
            ->where('user_id', $user->id)
            ->delete();
    }
}
