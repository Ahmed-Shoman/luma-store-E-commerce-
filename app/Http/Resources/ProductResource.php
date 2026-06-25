<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $isList = $request->routeIs('products.index');

        return [
            'id'            => $this->id,

            'name'          => $this->name_ar,
            'nameEn'        => $this->name_en,

            'price'         => (float) $this->price,
            'originalPrice' => $this->original_price ? (float) $this->original_price : null,

            // ⚡ Descriptions — omitted on list view to keep payload small
            'description'   => $this->when(! $isList, $this->description_ar),
            'descriptionEn' => $this->when(! $isList, $this->description_en),

            // ⚡ Only one image on list, full array on detail
            'image'  => $isList
                ? optional($this->images->first())->url
                : null,

            'images' => $this->when(
                ! $isList,
                fn() => $this->images->map(fn($img) => $img->url)
            ),

            'category' => $this->whenLoaded('category', function () {
                return [
                    'id'    => $this->category->id,
                    'name'  => $this->category->name_en,
                    'nameAr' => $this->category->name_ar,
                ];
            }),

            // ⚡ Reel Video
            'reelVideo' => $this->when(! $isList, $this->reel_url),

            'isNew'       => $this->is_new_arrival,
            'isBestSeller' => $this->is_best_seller,
            'isTrending'  => $this->is_trending,

            'createdAt'   => $this->created_at?->toDateTimeString(),
        ];
    }
}
