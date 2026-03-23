<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\CatalogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ProductController extends Controller
{
    public function __construct(
        protected CatalogService $catalogService,
    ) {
    }

    /**
     * Display a listing of the products.
     */
    public function index(Request $request)
    {
        $filters = $request->validate([
            'q' => 'nullable|string|max:255',
            'category' => 'nullable|string|max:255',
            'sort' => 'nullable|in:popular,new,price_asc,price_desc,name_asc',
            'min_price' => 'nullable|numeric|min:0',
            'max_price' => 'nullable|numeric|min:0',
            'on_sale' => 'nullable|boolean',
        ]);

        return Inertia::render('index', $this->catalogService->getCatalogPage(Auth::user(), $filters));
    }

    /**
     * Display the specified product.
     */
    public function show(Product $product): \Inertia\Response
    {
        return Inertia::render('product', $this->catalogService->getProductPage($product, Auth::user()));
    }
}
