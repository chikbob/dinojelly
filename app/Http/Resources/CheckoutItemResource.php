<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CheckoutItemResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->product->id,
            'name' => $this->product->name,
            'price' => $this->product->price,
            'quantity' => $this->quantity,
            'image_url' => $this->product->image_url,
            'subtotal' => $this->product->price * $this->quantity,
            'available_quantity' => $this->product->stockItem
                ? max(0, (int) $this->product->stockItem->quantity - (int) $this->product->stockItem->reserved_quantity)
                : 0,
        ];
    }
}
