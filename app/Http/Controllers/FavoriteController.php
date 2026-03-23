<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\CatalogService;
use Inertia\Inertia;

class FavoriteController extends Controller
{
    public function __construct(
        protected CatalogService $catalogService,
    ) {
    }

    public function index(Request $request)
    {
        $sort = $request->get('order', 'created_at_desc');
        return Inertia::render('favorites', $this->catalogService->getFavoritesPage($request->user(), $sort));
    }

    public function toggle(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        $this->catalogService->toggleFavorite($request->user(), (int) $request->product_id);

        return back();
    }
}
