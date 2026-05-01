<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderListResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'source_subscription' => $this->whenLoaded('sourceSubscriptions', function () {
                $subscription = $this->pickSourceSubscription();

                return $subscription ? [
                    'id' => $subscription->id,
                    'name' => $subscription->name,
                    'status' => $subscription->status,
                ] : null;
            }),
            'id' => $this->id,
            'total_price' => $this->total_price,
            'delivery_price' => $this->delivery_price,
            'discount_amount' => $this->discount_amount,
            'gift_card_amount' => $this->gift_card_amount,
            'referral_credit_amount' => $this->referral_credit_amount,
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
            'latest_payment' => $this->whenLoaded('latestPayment', function () use ($request) {
                return PaymentResource::make($this->latestPayment)->resolve($request);
            }),
        ];
    }

    protected function pickSourceSubscription(): mixed
    {
        return $this->sourceSubscriptions
            ->sortBy([
                fn ($left, $right) => $this->subscriptionPriority($left) <=> $this->subscriptionPriority($right),
            ])
            ->first();
    }

    protected function subscriptionPriority(mixed $subscription): int
    {
        return match ($subscription->status) {
            'active' => 0,
            'paused' => 1,
            'canceled' => 2,
            default => 3,
        };
    }
}
