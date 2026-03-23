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
            'category' => $this->whenLoaded('category', function () use ($request) {
                return CategoryResource::make($this->category)->resolve($request);
            }),
        ];
    }
}
