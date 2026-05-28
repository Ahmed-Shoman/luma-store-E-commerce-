<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'productId' => $this->product_id,
            'name' => $this->product_name,
            'nameEn' => $this->product_name_en,
            'size' => $this->size,
            'quantity' => $this->quantity,
            'price' => $this->price,
            'total' => $this->total,
        ];
    }
}
