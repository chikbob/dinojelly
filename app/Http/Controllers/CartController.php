<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CartController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $cartItems = CartItem::where('user_id', $user->id)
            ->with('product')
            ->get();

        $cart = $cartItems->mapWithKeys(function ($item) {
            return [
                $item->product_id => [
                    'id' => $item->product->id,
                    'name' => $item->product->name,
                    'price' => $item->product->price,
                    'old_price' => $item->product->old_price,
                    'quantity' => $item->quantity,
                    'image_url' => $item->product->image_url,
                ]
            ];
        })->toArray();

        $favorites = $user->favorites()->pluck('product_id')->toArray();

        $user = $request->user();

        $ordersCount = Order::where('user_id', $user->id)
            ->where('status', 'pending')
            ->count();

        return Inertia::render('cart', [
            'cart' => $cart,
            'favorites' => $favorites,
            'pendingOrdersCount' => $ordersCount,
            'cartCount' => array_sum(array_column($cart, 'quantity')),
        ]);
    }

    public function add(Request $request)
    {
        $data = $request->validate([
            'product_id' => 'required|integer|exists:products,id',
        ]);

        $cartItem = CartItem::firstOrCreate(
            ['user_id' => $request->user()->id, 'product_id' => $data['product_id']],
            ['quantity' => 0]
        );

        $cartItem->increment('quantity');

        return back();
    }

    public function increase(Request $request)
    {
        $request->validate(['product_id' => 'required|exists:products,id']);

        $cartItem = CartItem::where('user_id', $request->user()->id)
            ->where('product_id', $request->product_id)
            ->first();

        if ($cartItem) {
            $cartItem->increment('quantity');
        }

        return back();
    }

    public function decrease(Request $request)
    {
        $request->validate(['product_id' => 'required|exists:products,id']);

        $cartItem = CartItem::where('user_id', $request->user()->id)
            ->where('product_id', $request->product_id)
            ->first();

        if ($cartItem) {
            if ($cartItem->quantity > 1) {
                $cartItem->decrement('quantity');
            } else {
                $cartItem->delete();
            }
        }

        return back();
    }

    public function remove(Request $request)
    {
        $request->validate(['id' => 'required|integer|exists:products,id']);

        CartItem::where('user_id', $request->user()->id)
            ->where('product_id', $request->id)
            ->delete();

        return back();
    }

    public function clear(Request $request)
    {
        CartItem::where('user_id', $request->user()->id)->delete();

        return back();
    }
}
