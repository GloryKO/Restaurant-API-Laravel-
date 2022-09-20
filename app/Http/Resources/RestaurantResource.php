<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class RestaurantResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'address_line_1' => $this->address_line_1,
            'address_line_2' => $this->address_line_2,
            'pincode' => $this->pincode,
            'city' => $this->city,
            'state_id' => $this->state_id,
            'country_id' => $this->country_id,
        ];
    }
}