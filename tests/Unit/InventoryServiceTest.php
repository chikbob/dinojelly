<?php

namespace Tests\Unit;

use App\Models\StockItem;
use App\Services\InventoryService;
use PHPUnit\Framework\TestCase;

class InventoryServiceTest extends TestCase
{
    public function test_it_calculates_available_quantity_and_purchase_limits(): void
    {
        $service = new InventoryService;
        $stockItem = new StockItem([
            'quantity' => 10,
            'reserved_quantity' => 4,
            'low_stock_threshold' => 5,
            'is_active' => true,
        ]);

        $this->assertSame(6, $service->getAvailableQuantity($stockItem));
        $this->assertTrue($service->canPurchaseQuantity($stockItem, 6));
        $this->assertFalse($service->canPurchaseQuantity($stockItem, 7));
        $this->assertFalse($service->isLowStock($stockItem));
    }

    public function test_inactive_or_missing_stock_is_not_available(): void
    {
        $service = new InventoryService;
        $inactiveStockItem = new StockItem([
            'quantity' => 10,
            'reserved_quantity' => 0,
            'low_stock_threshold' => 5,
            'is_active' => false,
        ]);

        $this->assertSame(0, $service->getAvailableQuantity(null));
        $this->assertSame(0, $service->getAvailableQuantity($inactiveStockItem));
        $this->assertFalse($service->canPurchaseQuantity($inactiveStockItem, 1));
        $this->assertFalse($service->isLowStock($inactiveStockItem));
    }
}
