<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Models\Product;
use App\Models\CartItem;

class ProductController extends Controller
{
    /**
     * Display a listing of the products.
     */
    public function index()
    {
        $user = Auth::user();
        $favoriteIds = [];
        $orders = collect(); // ✅ пустая коллекция по умолчанию

        if ($user) {
            $orders = Order::where('user_id', $user->id)
                ->with('items.product')
                ->orderByDesc('created_at')
                ->get();

            $favoriteIds = $user->favoriteProducts()->pluck('products.id')->toArray();
        }

        $favorites = auth()->check()
            ? auth()->user()->favorites()->pluck('product_id')
            : collect();

        $cartItems = [];
        $cartCount = 0;

        if ($user) {
            $cartItems = CartItem::where('user_id', $user->id)
                ->with('product')
                ->get()
                ->mapWithKeys(function ($item) use (&$cartCount) {
                    $cartCount += $item->quantity;
                    return [
                        $item->product_id => [
                            'id' => $item->product->id,
                            'name' => $item->product->name,
                            'price' => $item->product->price,
                            'quantity' => $item->quantity,
                            'image_url' => $item->product->image_url,
                        ]
                    ];
                })
                ->toArray();
        }

        // ✅ исправлено: теперь не ломается, если пользователь не авторизован
        $ordersCount = 0;
        if ($user) {
            $ordersCount = Order::where('user_id', $user->id)
                ->where('status', 'pending')
                ->count();
        }

        return Inertia::render('index', [
            'products' => Product::paginate(24)->through(function ($product) use ($favoriteIds) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'weight' => $product->weight,
                    'price' => $product->price,
                    'old_price' => $product->old_price,
                    'description' => $product->description,
                    'image_url' => $product->image_url,
                    'is_favorite' => in_array($product->id, $favoriteIds),
                ];
            }),
            'favorites' => $favorites,
            'cartItems' => $cartItems,
            'cartCount' => $cartCount,
            'pendingOrdersCount' => $ordersCount,
        ]);
    }

    /**
     * Display the specified product.
     */
    public function show(Product $product): \Inertia\Response
    {
        $orders = collect(); // ✅ пустая коллекция по умолчанию
        $isFavorite = false;
        if (Auth::check()) {
            $isFavorite = $product->favoritedByUsers()->where('user_id', Auth::id())->exists();
        }

        $favorites = auth()->check()
            ? auth()->user()->favorites()->pluck('product_id')
            : collect();

        $user = Auth::user();

        // ✅ исправлено: теперь не ломается, если пользователь не авторизован
        $ordersCount = 0;
        if ($user) {
            $ordersCount = Order::where('user_id', $user->id)
                ->where('status', 'pending')
                ->count();
        }

        // ✅ корзина теперь тоже из БД
        $cartItems = [];
        $cartCount = 0;
        if (Auth::check()) {
            $cartItems = CartItem::where('user_id', Auth::id())
                ->with('product')
                ->get()
                ->mapWithKeys(function ($item) use (&$cartCount) {
                    $cartCount += $item->quantity; // ✅ считаем общее количество
                    return [
                        $item->product_id => [
                            'id' => $item->product->id,
                            'name' => $item->product->name,
                            'price' => $item->product->price,
                            'quantity' => $item->quantity,
                            'image_url' => $item->product->image_url,
                        ]
                    ];
                })
                ->toArray();
        }

        return Inertia::render('product', [
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'weight' => $product->weight,
                'price' => $product->price,
                'old_price' => $product->old_price,
                'description' => $product->description,
                'image_url' => $product->image_url,
                'is_favorite' => $isFavorite,
            ],
            'favorites' => $favorites,
            'cartItems' => $cartItems,
            'cartCount' => $cartCount,
            'pendingOrdersCount' => $ordersCount,
        ]);
    }
}
