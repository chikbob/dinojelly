<?php

namespace App\Services;

use App\Http\Resources\OrderDetailResource;
use App\Http\Resources\OrderListResource;
use App\Models\Order;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;

class OrderService
{
    public function __construct(
        protected PaymentService $paymentService,
        protected OrderEventService $orderEventService,
        protected InventoryService $inventoryService,
    ) {
    }

    public function getPendingOrdersCount(User $user): int
    {
        return (int) Order::query()
            ->where('user_id', $user->id)
            ->where('status', 'pending')
            ->count();
    }

    /**
     * @return array{orders: array<int, array<string, mixed>>, filters: array{status: string|null}}
     */
    public function getOrdersPage(User $user, ?string $status): array
    {
        $ordersQuery = Order::query()
            ->where('user_id', $user->id)
            ->with(['items.product', 'address', 'deliverySlot', 'latestPayment'])
            ->orderByDesc('created_at');

        if ($status) {
            $ordersQuery->where('status', $status);
        }

        $orders = $ordersQuery->get();

        return [
            'orders' => OrderListResource::collection($orders)->resolve(request()),
            'filters' => [
                'status' => $status,
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function getOrderDetail(User $user, Order $order): array
    {
        $this->assertOwnership($user, $order);

        $order->load(['items.product', 'address', 'deliverySlot', 'latestPayment']);

        return OrderDetailResource::make($order)->resolve(request());
    }

    /**
     * @throws AuthorizationException
     */
    public function cancel(User $user, Order $order): void
    {
        $this->assertOwnership($user, $order);

        if ($order->status !== 'pending') {
            throw new \RuntimeException('Можно отменять только заказы в обработке');
        }

        $order->update([
            'status' => 'canceled',
        ]);

        $this->paymentService->cancelPendingPayments($order);
        $this->inventoryService->releaseOrderStock($order);
        $this->orderEventService->log(
            $order,
            'order_canceled',
            'Заказ отменен',
            'Пользователь отменил заказ',
            $user,
        );
    }

    /**
     * @throws AuthorizationException
     */
    protected function assertOwnership(User $user, Order $order): void
    {
        if ($order->user_id !== $user->id) {
            throw new AuthorizationException('Вы не можете работать с этим заказом');
        }
    }
}
