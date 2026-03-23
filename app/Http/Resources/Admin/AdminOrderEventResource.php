<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminOrderEventResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'title' => $this->title,
            'message' => $this->message,
            'meta' => $this->meta,
            'created_at' => $this->created_at,
            'actor' => $this->whenLoaded('actor', function () {
                return [
                    'id' => $this->actor?->id,
                    'name' => $this->actor?->name,
                    'email' => $this->actor?->email,
                ];
            }),
        ];
    }
}
