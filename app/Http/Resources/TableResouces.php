<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TableResources extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'extra_details'=>$this->extra_details,
            'number'=>$this->number,
            'state'=>$this->state,

        ];
    }
}
