<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $isList = $request->routeIs('categories.index');

        return [
            'id'      => $this->id,
            'name'    => $this->name_ar,
            'nameEn'  => $this->name_en,

            // ⚡ Only included when ?with_count=true was requested
            // Comes from withCount('products') in the controller
            'productCount' => $this->whenNotNull(
                isset($this->products_count) ? (int) $this->products_count : null
            ),

            // ⚡ Only on detail view (?with_products=true)
            // Avoids sending product data on list endpoints
            'products' => $this->when(
                ! $isList && $this->relationLoaded('products'),
                fn() => ProductResource::collection($this->products)
            ),

            'createdAt' => $this->when(
                ! $isList,
                fn() => $this->created_at?->toDateTimeString()
            ),
        ];
    }
}
