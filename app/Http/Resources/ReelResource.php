<?php


namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReelResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'video' => $this->video_url,

            'product' => $this->whenLoaded('product', function () {
                return [
                    'id' => $this->product->id,
                    'name' => $this->product->name_ar,
                    'image' => optional($this->product->images->first())->url,
                ];
            }),

            'createdAt' => $this->created_at?->toDateTimeString(),
        ];
    }
}
