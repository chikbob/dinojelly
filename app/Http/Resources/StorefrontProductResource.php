<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StorefrontProductResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'weight' => $this->weight,
            'price' => $this->price,
            'old_price' => $this->old_price,
            'description' => $this->description,
            'image_url' => $this->image_url,
            'average_rating' => $this->average_rating ? round((float) $this->average_rating, 1) : null,
            'reviews_count' => (int) ($this->reviews_count ?? 0),
            'available_quantity' => $this->stockItem
                ? max(0, (int) $this->stockItem->quantity - (int) $this->stockItem->reserved_quantity)
                : 0,
            'is_in_stock' => $this->stockItem
                ? $this->stockItem->is_active && ((int) $this->stockItem->quantity - (int) $this->stockItem->reserved_quantity) > 0
                : false,
            'is_low_stock' => $this->stockItem
                ? $this->stockItem->is_active && ((int) $this->stockItem->quantity - (int) $this->stockItem->reserved_quantity) <= (int) $this->stockItem->low_stock_threshold
                : false,
            'category' => $this->whenLoaded('category', function () use ($request) {
                return CategoryResource::make($this->category)->resolve($request);
            }),
        ];
    }
}
