<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'customer' => [
                'name' => $this->customer_name,
                'phone' => $this->customer_phone,
                'address' => $this->customer_address,
            ],

            'items' => OrderItemResource::collection($this->whenLoaded('items')),

            'total' => $this->total,
            'status' => $this->status,

            'itemsCount' => $this->items_count ?? $this->items->count(),

            'createdAt' => $this->created_at,
            'updatedAt' => $this->updated_at,
        ];
    }
}
