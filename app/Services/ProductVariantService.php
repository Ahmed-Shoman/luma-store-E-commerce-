<?php

namespace App\Services;

use App\Models\ProductVariant;

class ProductVariantService
{
    public function getVariantsByProduct($productId)
    {
        return ProductVariant::where('product_id', $productId)->get();
    }

    public function createVariant(array $data)
    {
        return ProductVariant::create($data);
    }

    public function updateVariant(ProductVariant $variant, array $data)
    {
        $variant->update($data);
        return $variant;
    }

    public function deleteVariant(ProductVariant $variant)
    {
        return $variant->delete();
    }
}