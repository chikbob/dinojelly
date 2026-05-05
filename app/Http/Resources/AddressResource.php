<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AddressResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'label' => $this->label,
            'recipient_name' => $this->recipient_name,
            'phone' => $this->phone,
            'city' => $this->city,
            'street' => $this->street,
            'building' => $this->building,
            'apartment' => $this->apartment,
            'entrance' => $this->entrance,
            'floor' => $this->floor,
            'postal_code' => $this->postal_code,
            'comment' => $this->comment,
            'is_default' => (bool) $this->is_default,
            'full_address' => trim(implode(', ', array_filter([
                $this->city,
                $this->street,
                $this->building,
                $this->apartment ? 'кв. '.$this->apartment : null,
            ]))),
        ];
    }
}
