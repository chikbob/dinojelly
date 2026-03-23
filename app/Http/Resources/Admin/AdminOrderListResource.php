<?php

namespace App\Http\Resources\Admin;

use App\Http\Resources\DeliverySlotResource;
use App\Http\Resources\PaymentResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminOrderListResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'total_price' => $this->total_price,
            'payment_method' => $this->payment_method,
            'created_at' => $this->created_at,
            'items_count' => $this->items_count,
            'customer' => [
                'id' => $this->user?->id,
                'name' => $this->user?->name,
                'email' => $this->user?->email,
                'phone' => $this->user?->phone,
            ],
            'delivery_slot' => $this->whenLoaded('deliverySlot', function () use ($request) {
                return DeliverySlotResource::make($this->deliverySlot)->resolve($request);
            }),
            'latest_payment' => $this->whenLoaded('latestPayment', function () use ($request) {
                return PaymentResource::make($this->latestPayment)->resolve($request);
            }),
        ];
    }
}
