<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                  => $this->id,

            // Names
            'name'                => $this->name_ar,
            'nameEn'              => $this->name_en,

            // Descriptions
            'description'         => $this->description_ar,
            'descriptionEn'       => $this->description_en,

            // Pricing
            'price'               => (float) $this->price,
            'originalPrice'       => $this->original_price ? (float) $this->original_price : null,

            // Images
            'image'               => $this->image,          // accessor → first image
            'images'              => $this->images ?? [],

            // Category
            'category'            => $this->category,

            // Flags
            'isNew'               => $this->is_new_arrival,
            'isBestSeller'        => $this->is_best_seller,
            'isTrending'          => $this->is_trending,

            // Computed (bonus)
            'isOnSale'            => $this->is_on_sale,
            'discountPercentage'  => $this->discount_percentage,

            // Timestamps
            'createdAt'           => $this->created_at->toDateTimeString(),
            'updatedAt'           => $this->updated_at->toDateTimeString(),
        ];
    }
}
