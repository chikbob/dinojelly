<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class ProductController extends Controller
{
    public function __construct(
        protected InventoryService $inventoryService,
    ) {}

    public function index()
    {
        $products = Product::query()
            ->with(['category', 'stockItem'])
            ->paginate(15);

        return Inertia::render('admin/Products/Index', ['products' => $products]);
    }

    public function create()
    {
        return Inertia::render('admin/Products/Create', [
            'categories' => Category::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(['id', 'name']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'nullable|exists:categories,id',
            'name' => 'required|string|max:255',
            'weight' => 'nullable|numeric|min:0',
            'price' => 'required|numeric|min:0',
            'old_price' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
            'sku' => 'required|string|max:255|unique:stock_items,sku',
            'stock_quantity' => 'required|integer|min:0',
            'low_stock_threshold' => 'nullable|integer|min:0',
            'stock_is_active' => 'nullable|boolean',
        ]);

        $productData = [
            'category_id' => $validated['category_id'] ?? null,
            'name' => $validated['name'],
            'weight' => $validated['weight'] ?? null,
            'price' => $validated['price'],
            'old_price' => $validated['old_price'] ?? null,
            'description' => $validated['description'] ?? null,
        ];

        if ($request->hasFile('image')) {
            $productData['image'] = $request->file('image')->store('products', 'public');
        }

        $product = Product::create($productData);
        $this->inventoryService->upsertForProduct($product->id, [
            'sku' => $validated['sku'],
            'quantity' => $validated['stock_quantity'],
            'low_stock_threshold' => $validated['low_stock_threshold'] ?? 5,
            'is_active' => (bool) ($validated['stock_is_active'] ?? true),
        ]);

        return redirect()->route('admin.products.index')->with('success', 'Product created');
    }

    public function edit(Product $product)
    {
        return Inertia::render('admin/Products/Edit', [
            'product' => $product->load(['category', 'stockItem']),
            'categories' => Category::query()
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(['id', 'name']),
        ]);
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'category_id' => 'nullable|exists:categories,id',
            'name' => 'required|string|max:255',
            'weight' => 'nullable|numeric|min:0',
            'price' => 'required|numeric|min:0',
            'old_price' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
            'image_url' => 'nullable|url',
            'sku' => 'required|string|max:255|unique:stock_items,sku,'.optional($product->stockItem)->id,
            'stock_quantity' => 'required|integer|min:0',
            'low_stock_threshold' => 'nullable|integer|min:0',
            'stock_is_active' => 'nullable|boolean',
        ]);

        $product->category_id = $validated['category_id'] ?? null;
        $product->name = $validated['name'];
        $product->weight = $validated['weight'] ?? null;
        $product->price = $validated['price'];
        $product->old_price = $validated['old_price'] ?? null;
        $product->description = $validated['description'] ?? '';

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('products', 'public');
            $product->image = $path;  // вот так
        } elseif (! empty($validated['image_url'])) {
            // Если пришла ссылка, можно либо сохранять в image, либо отдельно
            $product->image = $validated['image_url'];  // если хочешь хранить ссылку напрямую
        }

        $product->save();

        $this->inventoryService->upsertForProduct($product->id, [
            'sku' => $validated['sku'],
            'quantity' => $validated['stock_quantity'],
            'low_stock_threshold' => $validated['low_stock_threshold'] ?? 5,
            'is_active' => (bool) ($validated['stock_is_active'] ?? true),
        ]);

        return redirect()->route('admin.products.index')->with('success', 'Product updated!');
    }

    public function destroy(Product $product)
    {
        if ($product->image && ! filter_var($product->image, FILTER_VALIDATE_URL)) {
            Storage::disk('public')->delete($product->image);
        }

        $product->delete();

        return redirect()->route('admin.products.index')->with('success', 'Product deleted');
    }
}
