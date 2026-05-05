<?php

namespace App\Http\Controllers;

use App\Http\Resources\PaymentResource;
use App\Models\Order;
use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PaymentController extends Controller
{
    public function __construct(
        protected PaymentService $paymentService,
    ) {}

    public function showMock(Payment $payment, Request $request)
    {
        abort_unless($payment->order && $payment->order->user_id === $request->user()->id, 403);

        $payment->load('order');

        return Inertia::render('payments/mock', [
            'payment' => PaymentResource::make($payment)->resolve($request),
            'orderId' => $payment->order->id,
        ]);
    }

    public function retry(Order $order, Request $request)
    {
        $payment = $this->paymentService->retryForOrder($request->user(), $order->load('latestPayment'));
        $redirectUrl = $this->paymentService->getCheckoutRedirect($payment);

        if ($request->header('X-Inertia')) {
            return Inertia::location($redirectUrl ?? route('orders.show', $order));
        }

        return redirect()->away($redirectUrl ?? route('orders.show', $order));
    }
}
