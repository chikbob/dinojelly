<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Subscription;
use App\Services\CartService;
use App\Services\OrderService;
use App\Services\PaymentService;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SubscriptionController extends Controller
{
    public function __construct(
        protected SubscriptionService $subscriptionService,
        protected OrderService $orderService,
        protected CartService $cartService,
        protected PaymentService $paymentService,
    ) {
    }

    public function index(Request $request)
    {
        $user = $request->user();

        return Inertia::render('subscriptions/index', [
            ...$this->subscriptionService->getSubscriptionsPage($user),
            'pendingOrdersCount' => $this->orderService->getPendingOrdersCount($user),
            'cartCount' => $this->cartService->getCartCount($user),
        ]);
    }

    public function storeFromOrder(Request $request, Order $order)
    {
        $data = $request->validate([
            'name' => ['nullable', 'string', 'max:255'],
            'interval_days' => ['required', 'integer', 'min:7', 'max:90'],
        ]);

        try {
            $this->subscriptionService->createFromOrder($request->user(), $order, $data);

            return redirect()->route('subscriptions.index')
                ->with('success', 'Подписка создана');
        } catch (\Throwable $e) {
            return back()->withErrors(['subscription' => $e->getMessage()]);
        }
    }

    public function update(Request $request, Subscription $subscription)
    {
        $data = $request->validate([
            'name' => ['nullable', 'string', 'max:255'],
            'interval_days' => ['nullable', 'integer', 'min:7', 'max:90'],
            'status' => ['nullable', 'in:active,paused,canceled'],
        ]);

        try {
            $this->subscriptionService->update($request->user(), $subscription, $data);

            return redirect()->back()->with('success', 'Подписка обновлена');
        } catch (\Throwable $e) {
            return back()->withErrors(['subscription' => $e->getMessage()]);
        }
    }

    public function run(Request $request, Subscription $subscription)
    {
        try {
            $order = $this->subscriptionService->runNow($request->user(), $subscription);
            $payment = $order->latestPayment;

            if ($payment && $order->payment_method === 'card') {
                $redirectUrl = $this->paymentService->getCheckoutRedirect($payment);

                if ($request->header('X-Inertia')) {
                    return Inertia::location($redirectUrl);
                }

                return redirect()->away($redirectUrl);
            }

            return redirect()->route('orders.show', $order);
        } catch (\Throwable $e) {
            return back()->withErrors(['subscription' => $e->getMessage()]);
        }
    }
}
