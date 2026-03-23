<?php

namespace App\Http\Resources\Admin;

use App\Http\Resources\AddressResource;
use App\Http\Resources\DeliverySlotResource;
use App\Http\Resources\OrderItemResource;
use App\Http\Resources\PaymentResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminOrderDetailResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'payment_method' => $this->payment_method,
            'total_price' => $this->total_price,
            'delivery_price' => $this->delivery_price,
            'discount_amount' => $this->discount_amount,
            'total_quantity' => $this->total_quantity,
            'created_at' => $this->created_at,
            'customer' => [
                'id' => $this->user?->id,
                'name' => $this->user?->name,
                'email' => $this->user?->email,
                'phone' => $this->user?->phone,
            ],
            'address' => $this->whenLoaded('address', function () use ($request) {
                return AddressResource::make($this->address)->resolve($request);
            }),
            'delivery_slot' => $this->whenLoaded('deliverySlot', function () use ($request) {
                return DeliverySlotResource::make($this->deliverySlot)->resolve($request);
            }),
            'items' => OrderItemResource::collection($this->whenLoaded('items'))->resolve($request),
            'payments' => PaymentResource::collection($this->whenLoaded('payments'))->resolve($request),
            'latest_payment' => $this->whenLoaded('latestPayment', function () use ($request) {
                return PaymentResource::make($this->latestPayment)->resolve($request);
            }),
            'events' => AdminOrderEventResource::collection($this->whenLoaded('events'))->resolve($request),
        ];
    }
}
