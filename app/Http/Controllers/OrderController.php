<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class OrderController extends Controller
{
    /**
     * Страница оформления заказа (checkout)
     */
    public function create(Request $request)
    {
        $user = $request->user();
        $cartItems = CartItem::where('user_id', $user->id)->with('product')->get();

        if ($cartItems->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Корзина пуста');
        }

        $ordersCount = Order::where('user_id', $user->id)
            ->where('status', 'pending')
            ->count();

        $items = $cartItems->map(function ($item) {
            return [
                'id' => $item->product->id,
                'name' => $item->product->name,
                'price' => $item->product->price,
                'quantity' => $item->quantity,
                'image_url' => $item->product->image_url,
                'subtotal' => $item->product->price * $item->quantity,
            ];
        });

        $totalQuantity = $cartItems->sum('quantity');
        $totalPrice = $cartItems->sum(fn($item) => $item->product->price * $item->quantity);
        $cartCount = $cartItems->sum('quantity');

        return Inertia::render('checkout', [
            'items' => $items,
            'totalQuantity' => $totalQuantity,
            'totalPrice' => $totalPrice,
            'pendingOrdersCount' => $ordersCount,
            'cartCount' => $cartCount,
        ]);
    }

    /**
     * Создание заказа
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'payment_method' => 'required|in:card,cash',
        ]);

        $user = $request->user();
        $cartItems = CartItem::where('user_id', $user->id)->with('product')->get();

        if ($cartItems->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Корзина пуста');
        }

        try {
            DB::beginTransaction();

            $totalQuantity = $cartItems->sum('quantity');
            $totalPrice = $cartItems->sum(fn($item) => $item->product->price * $item->quantity);

            $order = Order::create([
                'user_id' => $user->id,
                'total_price' => $totalPrice,
                'total_quantity' => $totalQuantity,
                'payment_method' => $data['payment_method'],
                'status' => 'pending',
            ]);

            foreach ($cartItems as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'price' => $item->product->price,
                ]);
            }

            // очищаем корзину
            CartItem::where('user_id', $user->id)->delete();

            DB::commit();

            return redirect()->route('orders.show', $order->id)
                ->with('success', 'Заказ успешно оформлен!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['order' => 'Ошибка при создании заказа: ' . $e->getMessage()]);
        }
    }

    /**
     * Список всех заказов пользователя
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $status = $request->get('status');

        $ordersQuery = Order::where('user_id', $user->id)
            ->with('items.product')
            ->orderByDesc('created_at');

        if ($status) {
            $ordersQuery->where('status', $status);
        }

        $ordersCount = Order::where('user_id', $user->id)
            ->where('status', 'pending')
            ->count();

        $orders = $ordersQuery->get();

        $cartCount = CartItem::where('user_id', $user->id)->sum('quantity');

        return Inertia::render('orders/index', [
            'orders' => $orders,
            'pendingOrdersCount' => $ordersCount,
            'cartCount' => $cartCount,
            'filters' => [
                'status' => $status
            ]
        ]);
    }

    /**
     * Просмотр конкретного заказа
     */
    public function show(Order $order)
    {
        // Проверка, чтобы нельзя было смотреть чужие заказы
        if ($order->user_id !== auth()->id()) {
            abort(403, 'Вы не можете просматривать этот заказ');
        }

        $user = Auth::user();

        $orders = Order::where('user_id', $user->id)
            ->with('items.product')
            ->orderByDesc('created_at')
            ->get();

        $ordersCount = Order::where('user_id', $user->id)
            ->where('status', 'pending')
            ->count();

        $order->load('items.product');
        $cartCount = auth()->check()
            ? CartItem::where('user_id', auth()->id())->sum('quantity')
            : 0;

        return Inertia::render('orders/show', [
            'order' => $order,
            'orders' => $orders,
            'pendingOrdersCount' => $ordersCount,
            'cartCount' => $cartCount,
        ]);
    }

    /**
     * Отмена заказа
     */
    public function cancel(Order $order, Request $request)
    {
        // Проверка, чтобы нельзя было отменять чужие заказы
        if ($order->user_id !== auth()->id()) {
            abort(403, 'Вы не можете отменить этот заказ');
        }

        // Проверяем, можно ли отменить заказ (только pending можно отменить)
        if ($order->status !== 'pending') {
            return response()->json([
                'error' => 'Можно отменять только заказы в обработке'
            ], 422);
        }

        try {
            $order->update([
                'status' => 'canceled'
            ]);

            // Для Inertia лучше возвращать редирект или JSON
            if ($request->header('X-Inertia')) {
                return redirect()->back()->with('success', 'Заказ успешно отменен');
            }

            return response()->json([
                'success' => true,
                'message' => 'Заказ успешно отменен'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Ошибка при отмене заказа: ' . $e->getMessage()
            ], 500);
        }
    }
}
