<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request)
    {
        return parent::version($request);
    }

    public function share(Request $request)
    {
        $shared = parent::share($request);

        $cart = session('cart', []); // формат: ['1' => ['id'=>1,'quantity'=>2], ...]
        $cartCount = collect($cart)->sum('quantity');

        $favorites = auth()->check()
            ? auth()->user()->favorites()->pluck('product_id')->toArray()
            : [];

        return array_merge($shared, [
            // auth.user уже часто возвращается — при необходимости расширяй
            'cart' => $cart, // фронт может читать cart[productId].quantity
            'cartCount' => $cartCount,
            'favorites' => $favorites,
        ]);
    }
}
