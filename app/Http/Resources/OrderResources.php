<?php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderResources extends JsonResource{
    public function toArray($request):array{
        return [
            'id'=>$this->id,
            'table'=>new TableResources($this->whenLoaded('table')),
            'total'=>$this->total,
            'status' =>$this->getStatusLabel(),
            'items'=>OrderItemResources::collection($this->whenLoaded('items')),
        ];
    }
}

