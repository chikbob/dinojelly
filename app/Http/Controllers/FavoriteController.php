<?php

namespace App\Http\Controllers;

use App\Models\Favorite;
use App\Models\CartItem;
use App\Models\Order;
use Illuminate\Http\Request;
use Inertia\Inertia;

class FavoriteController extends Controller
{
    public function index(Request $request)
    {
        $order = $request->get('order', 'created_at_desc'); // дефолт: новые первыми

        $query = Favorite::query()
            ->where('user_id', $request->user()->id)
            ->join('products', 'favorites.product_id', '=', 'products.id')
            ->select('favorites.*', 'products.price as product_price'); // чтобы сортировка по цене работала

        switch ($order) {
            case 'created_at_asc':
                $query->orderBy('favorites.created_at', 'asc');
                break;
            case 'created_at_desc':
                $query->orderBy('favorites.created_at', 'desc');
                break;
            case 'price_asc':
                $query->orderBy('products.price', 'asc');
                break;
            case 'price_desc':
                $query->orderBy('products.price', 'desc');
                break;
        }

        $favorites = $query->with('product')
            ->paginate(24)
            ->through(function ($favorite) {
                return [
                    'id' => $favorite->product->id,
                    'name' => $favorite->product->name,
                    'weight' => $favorite->product->weight,
                    'price' => $favorite->product->price,
                    'old_price' => $favorite->product->old_price,
                    'description' => $favorite->product->description,
                    'image_url' => $favorite->product->image_url,
                ];
            });

        $cartCount = 0;
        $cartItems = CartItem::where('user_id', $request->user()->id)
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

        $user = $request->user();

        $ordersCount = Order::where('user_id', $user->id)
            ->where('status', 'pending')
            ->count();

        return Inertia::render('favorites', [
            'favorites' => $favorites,
            'cartItems' => $cartItems,
            'cartCount' => $cartCount,
            'order' => $order,
            'pendingOrdersCount' => $ordersCount,
        ]);
    }

    public function toggle(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        $favorite = Favorite::where('user_id', $request->user()->id)
            ->where('product_id', $request->product_id)
            ->first();

        if ($favorite) {
            $favorite->delete();
        } else {
            Favorite::create([
                'user_id' => $request->user()->id,
                'product_id' => $request->product_id,
            ]);
        }

        return back();
    }
}
