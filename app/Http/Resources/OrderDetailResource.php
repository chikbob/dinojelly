<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderDetailResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'total_price' => $this->total_price,
            'delivery_price' => $this->delivery_price,
            'discount_amount' => $this->discount_amount,
            'total_quantity' => $this->total_quantity,
            'payment_method' => $this->payment_method,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'address' => $this->whenLoaded('address', function () use ($request) {
                return AddressResource::make($this->address)->resolve($request);
            }),
            'delivery_slot' => $this->whenLoaded('deliverySlot', function () use ($request) {
                return DeliverySlotResource::make($this->deliverySlot)->resolve($request);
            }),
            'items' => OrderItemResource::collection($this->whenLoaded('items'))->resolve($request),
        ];
    }
}
