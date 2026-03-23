<?php

namespace App\Http\Controllers;

use App\Services\PaymentService;
use Illuminate\Http\Request;

class PaymentWebhookController extends Controller
{
    public function __construct(
        protected PaymentService $paymentService,
    ) {
    }

    public function mock(Request $request)
    {
        $payment = $this->paymentService->handleMockWebhook($request->validate([
            'provider_payment_id' => ['required', 'string'],
            'status' => ['required', 'in:paid,failed,canceled'],
        ]));

        return redirect()
            ->route('orders.show', $payment->order_id)
            ->with('success', 'Статус оплаты обновлен');
    }
}
