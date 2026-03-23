<?php

namespace App\Services;

use App\Models\CartItem;
use App\Models\Order;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;

class ReorderService
{
    public function __construct(
        protected InventoryService $inventoryService,
        protected AbandonedCartService $abandonedCartService,
    ) {
    }

    /**
     * @return array{added:int, skipped:int}
     *
     * @throws AuthorizationException
     */
    public function reorder(User $user, Order $order): array
    {
        if ($order->user_id !== $user->id) {
            throw new AuthorizationException('Вы не можете повторить чужой заказ');
        }

        $order->loadMissing('items.product.stockItem');

        $added = 0;
        $skipped = 0;

        foreach ($order->items as $item) {
            if (!$item->product) {
                $skipped++;
                continue;
            }

            $stockItem = $item->product->stockItem;
            $available = $this->inventoryService->getAvailableQuantity($stockItem);

            if (!$stockItem || !$stockItem->is_active || $available < 1) {
                $skipped++;
                continue;
            }

            $cartItem = CartItem::query()->firstOrCreate(
                ['user_id' => $user->id, 'product_id' => $item->product_id],
                ['quantity' => 0]
            );

            $targetQuantity = min($cartItem->quantity + $item->quantity, $available);

            if ($targetQuantity <= $cartItem->quantity) {
                $skipped++;
                continue;
            }

            $cartItem->update([
                'quantity' => $targetQuantity,
            ]);

            $added += $targetQuantity - $cartItem->getOriginal('quantity');
        }

        if ($added < 1) {
            throw new \RuntimeException('Не удалось добавить товары из прошлого заказа в корзину');
        }

        $this->abandonedCartService->markRecovered($user, 'reorder_to_cart');

        return [
            'added' => $added,
            'skipped' => $skipped,
        ];
    }
}
