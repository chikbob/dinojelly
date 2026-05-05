<?php

namespace App\Services;

use App\Models\Order;
use App\Models\StockItem;
use App\Models\User;
use Illuminate\Support\Collection;

class InventoryService
{
    public function ensureProductInventory(int $productId, ?array $attributes = null): StockItem
    {
        return StockItem::query()->firstOrCreate(
            ['product_id' => $productId],
            array_merge([
                'sku' => 'SKU-'.$productId,
                'quantity' => 0,
                'reserved_quantity' => 0,
                'low_stock_threshold' => 5,
                'is_active' => true,
            ], $attributes ?? [])
        );
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function upsertForProduct(int $productId, array $data): StockItem
    {
        $stockItem = $this->ensureProductInventory($productId, [
            'sku' => $data['sku'] ?? ('SKU-'.$productId),
        ]);

        $stockItem->update([
            'sku' => $data['sku'] ?? $stockItem->sku,
            'quantity' => (int) ($data['quantity'] ?? $stockItem->quantity),
            'low_stock_threshold' => (int) ($data['low_stock_threshold'] ?? $stockItem->low_stock_threshold),
            'is_active' => (bool) ($data['is_active'] ?? $stockItem->is_active),
        ]);

        return $stockItem->refresh();
    }

    public function getAvailableQuantity(?StockItem $stockItem): int
    {
        if (! $stockItem || ! $stockItem->is_active) {
            return 0;
        }

        return max(0, (int) $stockItem->quantity - (int) $stockItem->reserved_quantity);
    }

    public function canPurchaseQuantity(?StockItem $stockItem, int $quantity): bool
    {
        return $this->getAvailableQuantity($stockItem) >= $quantity;
    }

    public function isLowStock(?StockItem $stockItem): bool
    {
        if (! $stockItem || ! $stockItem->is_active) {
            return false;
        }

        return $this->getAvailableQuantity($stockItem) <= (int) $stockItem->low_stock_threshold;
    }

    public function reserveOrderStock(Order $order): void
    {
        $order->loadMissing('items.product.stockItem');

        foreach ($order->items as $item) {
            $stockItem = $item->product?->stockItem;
            if (! $stockItem) {
                continue;
            }

            $stockItem->increment('reserved_quantity', $item->quantity);
        }
    }

    public function releaseOrderStock(Order $order): void
    {
        $order->loadMissing('items.product.stockItem');

        foreach ($order->items as $item) {
            $stockItem = $item->product?->stockItem;
            if (! $stockItem) {
                continue;
            }

            $stockItem->decrement('reserved_quantity', min($stockItem->reserved_quantity, $item->quantity));
        }
    }

    public function commitOrderStock(Order $order): void
    {
        $order->loadMissing('items.product.stockItem');

        foreach ($order->items as $item) {
            $stockItem = $item->product?->stockItem;
            if (! $stockItem) {
                continue;
            }

            $stockItem->decrement('reserved_quantity', min($stockItem->reserved_quantity, $item->quantity));
            $stockItem->decrement('quantity', min($stockItem->quantity, $item->quantity));
        }
    }

    /**
     * @return array<int, string>
     */
    public function validateCartStock(User $user, Collection $cartItems): array
    {
        $errors = [];

        foreach ($cartItems as $item) {
            $stockItem = $item->product?->stockItem;

            if (! $stockItem || ! $stockItem->is_active) {
                $errors[] = "Товар {$item->product->name} недоступен";

                continue;
            }

            if (! $this->canPurchaseQuantity($stockItem, (int) $item->quantity)) {
                $errors[] = "Недостаточно остатка для товара {$item->product->name}";
            }
        }

        return $errors;
    }
}
