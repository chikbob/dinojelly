<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartItemResource extends JsonResource
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
            'old_price' => $this->product->old_price,
            'quantity' => $this->quantity,
            'image_url' => $this->product->image_url,
            'available_quantity' => $this->product->stockItem
                ? max(0, (int) $this->product->stockItem->quantity - (int) $this->product->stockItem->reserved_quantity)
                : 0,
        ];
    }
}
