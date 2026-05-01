<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubscriptionResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'source_order_id' => $this->source_order_id,
            'status' => $this->status,
            'payment_method' => $this->payment_method,
            'interval_days' => $this->interval_days,
            'next_run_at' => $this->next_run_at,
            'last_run_at' => $this->last_run_at,
            'canceled_at' => $this->canceled_at,
            'address' => $this->whenLoaded('address', function () use ($request) {
                return AddressResource::make($this->address)->resolve($request);
            }),
            'delivery_slot' => $this->whenLoaded('deliverySlot', function () use ($request) {
                return DeliverySlotResource::make($this->deliverySlot)->resolve($request);
            }),
            'last_order' => $this->whenLoaded('lastOrder', function () {
                return $this->lastOrder ? [
                    'id' => $this->lastOrder->id,
                    'status' => $this->lastOrder->status,
                    'created_at' => $this->lastOrder->created_at,
                    'latest_payment' => $this->lastOrder->latestPayment
                        ? PaymentResource::make($this->lastOrder->latestPayment)->resolve(request())
                        : null,
                ] : null;
            }),
            'items' => $this->whenLoaded('items', function () use ($request) {
                return $this->items->map(function ($item) use ($request) {
                    return [
                        'id' => $item->id,
                        'quantity' => $item->quantity,
                        'price' => $item->price,
                        'product' => $item->product
                            ? StorefrontProductResource::make($item->product)->resolve($request)
                            : null,
                    ];
                })->values()->all();
            }),
        ];
    }
}
