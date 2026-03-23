<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Services\InventoryService;
use App\Services\OrderEventService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PaymentController extends Controller
{
    public function __construct(
        protected InventoryService $inventoryService,
        protected OrderEventService $orderEventService,
    ) {
    }

    public function index()
    {
        $payments = Payment::query()
            ->with('order.user')
            ->latest()
            ->paginate(20)
            ->through(fn (Payment $payment) => [
                'id' => $payment->id,
                'provider' => $payment->provider,
                'provider_payment_id' => $payment->provider_payment_id,
                'amount' => $payment->amount,
                'currency' => $payment->currency,
                'status' => $payment->status,
                'method' => $payment->method,
                'paid_at' => $payment->paid_at,
                'created_at' => $payment->created_at,
                'order' => $payment->order ? [
                    'id' => $payment->order->id,
                    'customer_name' => $payment->order->user?->name,
                ] : null,
            ]);

        return Inertia::render('admin/Payments/Index', [
            'payments' => $payments,
        ]);
    }

    public function update(Request $request, Payment $payment)
    {
        $data = $request->validate([
            'status' => ['required', 'in:pending,paid,failed,canceled'],
        ]);

        $previous = $payment->status;
        if ($previous === $data['status']) {
            return redirect()->back();
        }

        $payment->update([
            'status' => $data['status'],
            'paid_at' => $data['status'] === 'paid' ? now() : null,
        ]);

        $order = $payment->order;
        if ($order) {
            $order->update([
                'status' => match ($data['status']) {
                    'paid' => 'completed',
                    'canceled' => 'canceled',
                    default => 'pending',
                },
            ]);

            match ($data['status']) {
                'paid' => $this->inventoryService->commitOrderStock($order),
                'failed', 'canceled' => $this->inventoryService->releaseOrderStock($order),
                default => null,
            };

            $this->orderEventService->log(
                $order,
                'payment_status_changed',
                'Статус платежа обновлен вручную',
                "Статус платежа изменен с {$previous} на {$data['status']}",
                $request->user(),
                [
                    'payment_id' => $payment->id,
                    'previous_status' => $previous,
                    'new_status' => $data['status'],
                ],
            );
        }

        return redirect()->back()->with('success', 'Payment updated');
    }
}
