<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\CartService;
use App\Services\OrderService;
use Inertia\Inertia;

class CartController extends Controller
{
    public function __construct(
        protected CartService $cartService,
        protected OrderService $orderService,
    ) {
    }

    public function index(Request $request)
    {
        $user = $request->user();

        return Inertia::render('cart', [
            'cart' => $this->cartService->getCartMap($user),
            'favorites' => $user->favorites()->pluck('product_id')->toArray(),
            'recovered' => $request->boolean('recovered'),
            'pendingOrdersCount' => $this->orderService->getPendingOrdersCount($user),
            'cartCount' => $this->cartService->getCartCount($user),
        ]);
    }

    public function add(Request $request)
    {
        $data = $request->validate([
            'product_id' => 'required|integer|exists:products,id',
        ]);

        $this->cartService->addProduct($request->user(), $data['product_id']);

        return back();
    }

    public function increase(Request $request)
    {
        $request->validate(['product_id' => 'required|exists:products,id']);
        $this->cartService->increaseQuantity($request->user(), (int) $request->product_id);

        return back();
    }

    public function decrease(Request $request)
    {
        $request->validate(['product_id' => 'required|exists:products,id']);
        $this->cartService->decreaseQuantity($request->user(), (int) $request->product_id);

        return back();
    }

    public function remove(Request $request)
    {
        $request->validate(['id' => 'required|integer|exists:products,id']);
        $this->cartService->removeProduct($request->user(), (int) $request->id);

        return back();
    }

    public function clear(Request $request)
    {
        $this->cartService->clear($request->user());

        return back();
    }
}
