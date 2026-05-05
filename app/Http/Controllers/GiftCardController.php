<?php

namespace App\Http\Controllers;

use App\Services\CartService;
use App\Services\GiftCardService;
use Illuminate\Http\Request;

class GiftCardController extends Controller
{
    public function __construct(
        protected GiftCardService $giftCardService,
        protected CartService $cartService,
    ) {}

    public function preview(Request $request)
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:32'],
            'delivery_price' => ['nullable', 'numeric', 'min:0'],
        ]);

        $cartItems = $this->cartService->getCartItems($request->user());
        $subtotal = (float) $cartItems->sum(fn ($item) => $item->product->price * $item->quantity);
        $total = $subtotal + (float) ($data['delivery_price'] ?? 0);

        try {
            return response()->json($this->giftCardService->preview($request->user(), $data['code'], $total));
        } catch (\Throwable $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function claim(Request $request)
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:32'],
        ]);

        try {
            $this->giftCardService->claim($request->user(), $data['code']);

            return back()->with('success', 'Подарочная карта добавлена в ваш аккаунт');
        } catch (\Throwable $e) {
            return back()->withErrors(['gift_card' => $e->getMessage()]);
        }
    }
}
