<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ProductRequest;
use App\Models\Product;
use App\Services\ProductService;

class ProductController extends Controller
{
    protected $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    public function index()
    {
        $products = $this->productService->getAllForAdmin();
        return $this->successResponse($products, 'تم جلب المنتجات بنجاح');
    }

    public function store(ProductRequest $request)
    {
        $product = $this->productService->createProduct($request->validated());
        return $this->successResponse($product, 'تم إضافة المنتج بنجاح', 201);
    }

    public function show(Product $product)
    {
        $product->load('category');
        return $this->successResponse($product, 'تفاصيل المنتج');
    }

    public function update(ProductRequest $request, Product $product)
    {
        $updatedProduct = $this->productService->updateProduct($product, $request->validated());
        return $this->successResponse($updatedProduct, 'تم تحديث المنتج بنجاح');
    }

    public function destroy(Product $product)
    {
        $this->productService->deleteProduct($product);
        return $this->successResponse(null, 'تم حذف المنتج بنجاح');
    }
}