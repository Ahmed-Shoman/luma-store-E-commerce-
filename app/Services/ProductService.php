<?php

namespace App\Services;

use App\Models\Product;
use App\Traits\UploadTrait;

class ProductService
{
    use UploadTrait;

    public function getAllForAdmin()
    {
        return Product::with('category')->get();
    }

    public function createProduct(array $data)
    {
        if (isset($data['image']) && request()->hasFile('image')) {
            $data['image'] = $this->uploadFile(request()->file('image'), 'products');
        }

        return Product::create($data);
    }

    public function updateProduct(Product $product, array $data)
    {
        if (isset($data['image']) && request()->hasFile('image')) {
            $this->deleteFile($product->image);
            $data['image'] = $this->uploadFile(request()->file('image'), 'products');
        }

        $product->update($data);
        return $product;
    }

    public function deleteProduct(Product $product)
    {
        $this->deleteFile($product->image);
        return $product->delete();
    }
}
