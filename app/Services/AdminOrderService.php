<?php

namespace App\Services;

use App\Http\Resources\Admin\AdminOrderDetailResource;
use App\Http\Resources\Admin\AdminOrderListResource;
use App\Models\Order;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class AdminOrderService
{
    public function __construct(
        protected OrderEventService $orderEventService,
        protected InventoryService $inventoryService,
        protected ReferralService $referralService,
        protected GiftCardService $giftCardService,
    ) {}

    /**
     * @param  array{status?: ?string, payment_status?: ?string, search?: ?string}  $filters
     * @return array{orders: LengthAwarePaginator, filters: array<string, mixed>}
     */
    public function getOrdersPage(array $filters): array
    {
        $query = Order::query()
            ->with(['user', 'deliverySlot', 'latestPayment'])
            ->withCount('items')
            ->orderByDesc('created_at');

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['payment_status'])) {
            $query->whereHas('latestPayment', function ($paymentQuery) use ($filters) {
                $paymentQuery->where('status', $filters['payment_status']);
            });
        }

        if (! empty($filters['search'])) {
            $search = trim((string) $filters['search']);
            $query->where(function ($builder) use ($search) {
                $builder
                    ->where('id', $search)
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery
                            ->where('name', 'like', '%'.$search.'%')
                            ->orWhere('email', 'like', '%'.$search.'%')
                            ->orWhere('phone', 'like', '%'.$search.'%');
                    });
            });
        }

        $orders = $query->paginate(15)->withQueryString();
        $orders->setCollection(
            collect(AdminOrderListResource::collection($orders->getCollection())->resolve(request()))
        );

        return [
            'orders' => $orders,
            'filters' => [
                'status' => $filters['status'] ?? null,
                'payment_status' => $filters['payment_status'] ?? null,
                'search' => $filters['search'] ?? null,
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function getOrderDetail(Order $order): array
    {
        $order->load([
            'user',
            'address',
            'deliverySlot',
            'items.product',
            'payments',
            'events.actor',
            'latestPayment',
        ]);

        return AdminOrderDetailResource::make($order)->resolve(request());
    }

    public function updateStatus(Order $order, string $status, ?User $actor = null): void
    {
        $previous = $order->status;

        if ($previous === $status) {
            return;
        }

        $order->update([
            'status' => $status,
        ]);

        if ($previous !== 'completed' && $status === 'completed') {
            $this->inventoryService->commitOrderStock($order);
            $this->referralService->completeForOrder($order);
        }

        if ($previous !== 'canceled' && $status === 'canceled') {
            $this->inventoryService->releaseOrderStock($order);
            $this->giftCardService->refundOrderDiscounts($order);
        }

        $this->orderEventService->log(
            $order,
            'status_changed',
            'Статус заказа обновлен',
            "Статус изменен с {$previous} на {$status}",
            $actor,
            [
                'previous_status' => $previous,
                'new_status' => $status,
            ],
        );
    }

    public function addNote(Order $order, User $actor, string $note): void
    {
        $this->orderEventService->log(
            $order,
            'note_added',
            'Добавлена заметка менеджера',
            $note,
            $actor,
        );
    }
}
