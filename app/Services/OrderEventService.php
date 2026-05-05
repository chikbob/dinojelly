<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderEvent;
use App\Models\User;

class OrderEventService
{
    /**
     * @param  array<string, mixed>  $meta
     */
    public function log(
        Order $order,
        string $type,
        string $title,
        ?string $message = null,
        ?User $actor = null,
        array $meta = [],
    ): OrderEvent {
        return OrderEvent::query()->create([
            'order_id' => $order->id,
            'actor_user_id' => $actor?->id,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'meta' => $meta,
        ]);
    }
}
