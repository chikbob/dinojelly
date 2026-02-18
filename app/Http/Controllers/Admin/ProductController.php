<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::paginate(15);
        return Inertia::render('admin/Products/Index', ['products' => $products]);
    }

    public function create()
    {
        return Inertia::render('admin/Products/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            // остальные поля продукта
        ]);

        Product::create($validated);

        return redirect()->route('admin.products.index')->with('success', 'Product created');
    }

    public function edit(Product $product)
    {
        return Inertia::render('admin/Products/Edit', ['product' => $product]);
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'weight' => 'nullable|numeric|min:0',
            'price' => 'required|numeric|min:0',
            'old_price' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
            'image_url' => 'nullable|url',
        ]);

        $product->name = $validated['name'];
        $product->weight = $validated['weight'] ?? null;
        $product->price = $validated['price'];
        $product->old_price = $validated['old_price'] ?? null;
        $product->description = $validated['description'] ?? '';

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('products', 'public');
            $product->image = $path;  // вот так
        } elseif (!empty($validated['image_url'])) {
            // Если пришла ссылка, можно либо сохранять в image, либо отдельно
            $product->image = $validated['image_url'];  // если хочешь хранить ссылку напрямую
        }

        $product->save();

        return redirect()->route('admin.products.index')->with('success', 'Product updated!');
    }


    public function destroy(Product $product)
    {
        $product->delete();

        return redirect()->route('admin.products.index')->with('success', 'Product deleted');
    }
}
