<?php

namespace App\Services;

use App\Models\CartItem;
use App\Models\Order;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;

class ReorderService
{
    public function __construct(
        protected InventoryService $inventoryService,
        protected AbandonedCartService $abandonedCartService,
    ) {}

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
        $targetItems = [];

        foreach ($order->items as $item) {
            if (! $item->product) {
                $skipped++;

                continue;
            }

            $stockItem = $item->product->stockItem;
            $available = $this->inventoryService->getAvailableQuantity($stockItem);

            if (! $stockItem || ! $stockItem->is_active || $available < 1) {
                $skipped++;

                continue;
            }
            $targetQuantity = min($item->quantity, $available);

            if ($targetQuantity < 1) {
                $skipped++;

                continue;
            }

            $targetItems[] = [
                'user_id' => $user->id,
                'product_id' => $item->product_id,
                'quantity' => $targetQuantity,
                'created_at' => now(),
                'updated_at' => now(),
            ];
            $added += $targetQuantity;

            if ($targetQuantity < $item->quantity) {
                $skipped++;
            }
        }

        if ($added < 1) {
            throw new \RuntimeException('Не удалось добавить товары из прошлого заказа в корзину');
        }

        DB::transaction(function () use ($user, $targetItems) {
            CartItem::query()
                ->where('user_id', $user->id)
                ->delete();

            CartItem::query()->insert($targetItems);
        });

        $this->abandonedCartService->markRecovered($user, 'reorder_to_checkout');

        return [
            'added' => $added,
            'skipped' => $skipped,
        ];
    }
}
