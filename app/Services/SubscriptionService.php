<?php

namespace App\Services;

use App\Http\Resources\SubscriptionResource;
use App\Models\DeliverySlot;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Subscription;
use App\Models\SubscriptionItem;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;

class SubscriptionService
{
    public function __construct(
        protected PaymentService $paymentService,
        protected InventoryService $inventoryService,
        protected OrderEventService $orderEventService,
    ) {
    }

    /**
     * @return array{subscriptions: array<int, array<string, mixed>>}
     */
    public function getSubscriptionsPage(User $user): array
    {
        $subscriptions = Subscription::query()
            ->where('user_id', $user->id)
            ->with([
                'address',
                'deliverySlot',
                'items.product.stockItem',
                'lastOrder.latestPayment',
            ])
            ->latest()
            ->get();

        return [
            'subscriptions' => SubscriptionResource::collection($subscriptions)->resolve(request()),
        ];
    }

    /**
     * @param array{name?: ?string, interval_days:int} $data
     */
    public function createFromOrder(User $user, Order $order, array $data): Subscription
    {
        $this->assertOrderOwnership($user, $order);

        $order->loadMissing(['items.product', 'address', 'deliverySlot']);

        if ($order->items->isEmpty()) {
            throw new \RuntimeException('Нельзя создать подписку на пустой заказ');
        }

        if (!$order->address_id || !$order->delivery_slot_id) {
            throw new \RuntimeException('Для подписки нужен адрес и слот доставки');
        }

        return DB::transaction(function () use ($order, $data) {
            $subscription = Subscription::query()->create([
                'user_id' => $order->user_id,
                'address_id' => $order->address_id,
                'delivery_slot_id' => $order->delivery_slot_id,
                'last_order_id' => $order->id,
                'name' => $data['name'] ?: 'Подписка на заказ #' . $order->id,
                'payment_method' => $order->payment_method,
                'status' => 'active',
                'interval_days' => $data['interval_days'],
                'next_run_at' => now()->addDays($data['interval_days']),
            ]);

            foreach ($order->items as $item) {
                SubscriptionItem::query()->create([
                    'subscription_id' => $subscription->id,
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                ]);
            }

            return $subscription->load(['address', 'deliverySlot', 'items.product', 'lastOrder.latestPayment']);
        });
    }

    /**
     * @param array{name?: ?string, interval_days?: ?int, status?: ?string} $data
     *
     * @throws AuthorizationException
     */
    public function update(User $user, Subscription $subscription, array $data): Subscription
    {
        $this->assertSubscriptionOwnership($user, $subscription);

        $update = [];

        if (array_key_exists('name', $data)) {
            $update['name'] = $data['name'] ?: $subscription->name;
        }

        if (!empty($data['interval_days'])) {
            $update['interval_days'] = (int) $data['interval_days'];
        }

        if (!empty($data['status'])) {
            $update['status'] = $data['status'];
            $update['canceled_at'] = $data['status'] === 'canceled' ? now() : null;

            if ($data['status'] === 'active' && !$subscription->next_run_at) {
                $update['next_run_at'] = now()->addDays((int) ($data['interval_days'] ?? $subscription->interval_days));
            }
        }

        $subscription->update($update);

        return $subscription->refresh()->load(['address', 'deliverySlot', 'items.product.stockItem', 'lastOrder.latestPayment']);
    }

    /**
     * @throws AuthorizationException
     */
    public function runNow(User $user, Subscription $subscription): Order
    {
        $this->assertSubscriptionOwnership($user, $subscription);

        return $this->generateOrder($subscription, true);
    }

    public function processDueSubscriptions(int $limit = 25): int
    {
        $subscriptions = Subscription::query()
            ->where('status', 'active')
            ->whereNotNull('next_run_at')
            ->where('next_run_at', '<=', now())
            ->with(['items.product.stockItem', 'address', 'deliverySlot'])
            ->limit($limit)
            ->get();

        $processed = 0;

        foreach ($subscriptions as $subscription) {
            try {
                $this->generateOrder($subscription);
                $processed++;
            } catch (\Throwable) {
                $subscription->update([
                    'status' => 'paused',
                ]);
            }
        }

        return $processed;
    }

    public function generateOrder(Subscription $subscription, bool $manualRun = false): Order
    {
        $subscription->loadMissing(['items.product.stockItem', 'address', 'deliverySlot', 'user']);

        if ($subscription->status === 'canceled') {
            throw new \RuntimeException('Подписка отменена');
        }

        if (!$subscription->address) {
            throw new \RuntimeException('Для подписки не найден адрес доставки');
        }

        $deliverySlot = DeliverySlot::query()
            ->where('is_active', true)
            ->find($subscription->delivery_slot_id);

        if (!$deliverySlot) {
            throw new \RuntimeException('Слот доставки для подписки недоступен');
        }

        $subscriptionItems = $subscription->items->filter(fn ($item) => $item->product);

        if ($subscriptionItems->isEmpty()) {
            throw new \RuntimeException('В подписке нет доступных товаров');
        }

        foreach ($subscriptionItems as $item) {
            if (!$this->inventoryService->canPurchaseQuantity($item->product->stockItem, $item->quantity)) {
                throw new \RuntimeException('Недостаточно остатка для подписки');
            }
        }

        return DB::transaction(function () use ($subscription, $subscriptionItems, $deliverySlot, $manualRun) {
            $totalQuantity = $subscriptionItems->sum('quantity');
            $subtotal = $subscriptionItems->sum(fn ($item) => $item->product->price * $item->quantity);

            $order = Order::query()->create([
                'user_id' => $subscription->user_id,
                'subscription_id' => $subscription->id,
                'address_id' => $subscription->address_id,
                'delivery_slot_id' => $deliverySlot->id,
                'total_price' => $subtotal + $deliverySlot->price,
                'delivery_price' => $deliverySlot->price,
                'discount_amount' => 0,
                'total_quantity' => $totalQuantity,
                'payment_method' => $subscription->payment_method,
                'status' => 'pending',
            ]);

            foreach ($subscriptionItems as $item) {
                OrderItem::query()->create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'price' => $item->product->price,
                ]);
            }

            $payment = $this->paymentService->createForOrder($order);
            $this->inventoryService->reserveOrderStock($order);

            $subscription->update([
                'last_order_id' => $order->id,
                'last_run_at' => now(),
                'next_run_at' => now()->addDays($subscription->interval_days),
                'status' => 'active',
            ]);

            $this->orderEventService->log(
                $order,
                'subscription_order_created',
                'Заказ создан из подписки',
                $manualRun ? 'Пользователь запустил подписку вручную' : 'Подписка была выполнена по расписанию',
                $manualRun ? $subscription->user : null,
                [
                    'subscription_id' => $subscription->id,
                    'payment_id' => $payment->id,
                ],
            );

            return $order->load('latestPayment');
        });
    }

    /**
     * @throws AuthorizationException
     */
    protected function assertSubscriptionOwnership(User $user, Subscription $subscription): void
    {
        if ($subscription->user_id !== $user->id) {
            throw new AuthorizationException('Вы не можете работать с этой подпиской');
        }
    }

    /**
     * @throws AuthorizationException
     */
    protected function assertOrderOwnership(User $user, Order $order): void
    {
        if ($order->user_id !== $user->id) {
            throw new AuthorizationException('Вы не можете работать с этим заказом');
        }
    }
}
