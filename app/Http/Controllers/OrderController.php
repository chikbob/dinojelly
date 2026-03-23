<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\CheckoutService;
use App\Services\CartService;
use App\Services\OrderService;
use App\Services\PaymentService;
use App\Services\ReorderService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class OrderController extends Controller
{
    public function __construct(
        protected CheckoutService $checkoutService,
        protected OrderService $orderService,
        protected CartService $cartService,
        protected PaymentService $paymentService,
        protected ReorderService $reorderService,
    ) {
    }

    /**
     * Страница оформления заказа (checkout)
     */
    public function create(Request $request)
    {
        $payload = $this->checkoutService->getCheckoutPage($request->user());
        if (!$payload) {
            return redirect()->route('cart.index')->with('error', 'Корзина пуста');
        }

        return Inertia::render('checkout', $payload);
    }

    /**
     * Создание заказа
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'payment_method' => 'required|in:card,cash',
            'address_id' => 'required|integer|exists:addresses,id',
            'delivery_slot_id' => 'required|integer|exists:delivery_slots,id',
        ]);

        try {
            $order = $this->checkoutService->createOrder(
                $request->user(),
                $data['payment_method'],
                (int) $data['address_id'],
                (int) $data['delivery_slot_id'],
            );
            $payment = $order->latestPayment;

            if ($payment && $order->payment_method === 'card') {
                $redirectUrl = $this->paymentService->getCheckoutRedirect($payment);

                if ($request->header('X-Inertia')) {
                    return Inertia::location($redirectUrl);
                }

                return redirect()->away($redirectUrl)
                    ->with('success', 'Заказ создан. Завершите оплату.');
            }

            return redirect()->route('orders.show', $order->id)
                ->with('success', 'Заказ успешно оформлен!');
        } catch (\Exception $e) {
            return back()->withErrors(['order' => 'Ошибка при создании заказа: ' . $e->getMessage()]);
        }
    }

    /**
     * Список всех заказов пользователя
     */
    public function index(Request $request)
    {
        $status = $request->get('status');
        $user = $request->user();
        $payload = $this->orderService->getOrdersPage($user, $status);

        return Inertia::render('orders/index', [
            ...$payload,
            'pendingOrdersCount' => $this->orderService->getPendingOrdersCount($user),
            'cartCount' => $this->cartService->getCartCount($user),
        ]);
    }

    /**
     * Просмотр конкретного заказа
     */
    public function show(Order $order)
    {
        $user = request()->user();

        return Inertia::render('orders/show', [
            'order' => $this->orderService->getOrderDetail($user, $order),
            'pendingOrdersCount' => $this->orderService->getPendingOrdersCount($user),
            'cartCount' => $this->cartService->getCartCount($user),
        ]);
    }

    /**
     * Отмена заказа
     */
    public function cancel(Order $order, Request $request)
    {
        try {
            $this->orderService->cancel($request->user(), $order);

            if ($request->header('X-Inertia')) {
                return redirect()->back()->with('success', 'Заказ успешно отменен');
            }

            return response()->json([
                'success' => true,
                'message' => 'Заказ успешно отменен'
            ]);

        } catch (\Exception $e) {
            $status = match (true) {
                $e instanceof \Illuminate\Auth\Access\AuthorizationException => 403,
                $e instanceof \RuntimeException => 422,
                default => 500,
            };
            return response()->json([
                'error' => 'Ошибка при отмене заказа: ' . $e->getMessage()
            ], $status);
        }
    }

    public function reorder(Order $order, Request $request)
    {
        try {
            $result = $this->reorderService->reorder($request->user(), $order);

            return redirect()->route('cart.index')->with(
                'success',
                "В корзину добавлено {$result['added']} шт."
            );
        } catch (\Throwable $e) {
            return back()->withErrors(['reorder' => $e->getMessage()]);
        }
    }
}
