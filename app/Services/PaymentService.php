<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Str;

class PaymentService
{
    public function __construct(
        protected OrderEventService $orderEventService,
    ) {
    }

    public function createForOrder(Order $order): Payment
    {
        if ($order->payment_method === 'cash') {
            $payment = Payment::query()->create([
                'order_id' => $order->id,
                'provider' => 'offline',
                'provider_payment_id' => null,
                'amount' => $order->total_price,
                'currency' => 'RUB',
                'status' => 'pending',
                'method' => 'cash',
                'payload' => [
                    'label' => 'cash_on_delivery',
                ],
            ]);

            $this->orderEventService->log(
                $order,
                'payment_created',
                'Создан офлайн-платеж',
                'Заказ ожидает оплату наличными при получении',
                null,
                [
                    'provider' => 'offline',
                    'status' => $payment->status,
                    'method' => $payment->method,
                ],
            );

            return $payment;
        }

        $providerPaymentId = (string) Str::uuid();
        $payment = Payment::query()->create([
            'order_id' => $order->id,
            'provider' => 'mock',
            'provider_payment_id' => $providerPaymentId,
            'amount' => $order->total_price,
            'currency' => 'RUB',
            'status' => 'pending',
            'method' => 'card',
            'payload' => [],
        ]);

        $payment->forceFill([
            'payload' => [
                'payment_url' => route('payments.mock.show', $payment),
            ],
        ])->save();

        $this->orderEventService->log(
            $order,
            'payment_created',
            'Создан платеж',
            'Заказ ожидает завершения карточной оплаты',
            null,
            [
                'provider' => $payment->provider,
                'status' => $payment->status,
                'method' => $payment->method,
                'provider_payment_id' => $payment->provider_payment_id,
            ],
        );

        return $payment->refresh();
    }

    public function getCheckoutRedirect(Payment $payment): ?string
    {
        if ($payment->provider !== 'mock') {
            return null;
        }

        return $payment->payload['payment_url'] ?? route('payments.mock.show', $payment);
    }

    /**
     * @throws AuthorizationException
     */
    public function retryForOrder(User $user, Order $order): Payment
    {
        if ($order->user_id !== $user->id) {
            throw new AuthorizationException('Вы не можете работать с этим заказом');
        }

        if ($order->payment_method !== 'card') {
            throw new \RuntimeException('Повторная оплата доступна только для карточных заказов');
        }

        if ($order->status === 'completed') {
            throw new \RuntimeException('Заказ уже оплачен');
        }

        $latestPayment = $order->latestPayment;

        if ($latestPayment && $latestPayment->status === 'pending') {
            return $latestPayment;
        }

        if ($latestPayment && in_array($latestPayment->status, ['failed', 'canceled'], true)) {
            return $this->createForOrder($order);
        }

        return $latestPayment ?? $this->createForOrder($order);
    }

    public function cancelPendingPayments(Order $order): void
    {
        $order->payments()
            ->where('status', 'pending')
            ->update([
                'status' => 'canceled',
            ]);
    }

    public function handleMockWebhook(array $payload): Payment
    {
        $providerPaymentId = $payload['provider_payment_id'] ?? null;
        $status = $payload['status'] ?? null;

        if (!$providerPaymentId || !in_array($status, ['paid', 'failed', 'canceled'], true)) {
            throw new \InvalidArgumentException('Некорректный payload вебхука');
        }

        $payment = Payment::query()
            ->with('order')
            ->where('provider', 'mock')
            ->where('provider_payment_id', $providerPaymentId)
            ->firstOrFail();

        $payment->update([
            'status' => $status,
            'paid_at' => $status === 'paid' ? now() : null,
            'payload' => array_merge($payment->payload ?? [], [
                'last_webhook' => [
                    'status' => $status,
                    'received_at' => now()->toIso8601String(),
                ],
            ]),
        ]);

        $payment->order->update([
            'status' => match ($status) {
                'paid' => 'completed',
                'canceled' => 'canceled',
                default => 'pending',
            },
        ]);

        $this->orderEventService->log(
            $payment->order,
            'payment_status_changed',
            'Статус платежа обновлен',
            "Платеж переведен в статус {$status}",
            null,
            [
                'provider' => $payment->provider,
                'payment_id' => $payment->id,
                'provider_payment_id' => $payment->provider_payment_id,
                'status' => $status,
            ],
        );

        return $payment->refresh();
    }
}
