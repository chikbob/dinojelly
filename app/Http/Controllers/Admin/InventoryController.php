<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StockItem;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class InventoryController extends Controller
{
    public function __construct(
        protected InventoryService $inventoryService,
    ) {
    }

    public function index()
    {
        $stockItems = StockItem::query()
            ->with('product.category')
            ->orderBy('quantity')
            ->paginate(20)
            ->through(fn (StockItem $stockItem) => [
                'id' => $stockItem->id,
                'sku' => $stockItem->sku,
                'quantity' => $stockItem->quantity,
                'reserved_quantity' => $stockItem->reserved_quantity,
                'available_quantity' => max(0, $stockItem->quantity - $stockItem->reserved_quantity),
                'low_stock_threshold' => $stockItem->low_stock_threshold,
                'is_active' => $stockItem->is_active,
                'product' => $stockItem->product ? [
                    'id' => $stockItem->product->id,
                    'name' => $stockItem->product->name,
                    'category' => $stockItem->product->category?->name,
                ] : null,
            ]);

        return Inertia::render('admin/Inventory/Index', [
            'stockItems' => $stockItems,
        ]);
    }

    public function update(Request $request, StockItem $inventory)
    {
        $data = $request->validate([
            'sku' => ['required', 'string', 'max:255', 'unique:stock_items,sku,' . $inventory->id],
            'quantity' => ['required', 'integer', 'min:0'],
            'low_stock_threshold' => ['required', 'integer', 'min:0'],
            'is_active' => ['required', 'boolean'],
        ]);

        $inventory->update($data);

        return redirect()->back()->with('success', 'Inventory updated');
    }
}
