<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ProductVariantRequest;
use App\Models\ProductVariant;
use App\Services\ProductVariantService;

class ProductVariantController extends Controller
{
    protected $variantService;

    public function __construct(ProductVariantService $variantService)
    {
        $this->variantService = $variantService;
    }

    // هنا الـ index هيرجع كل المتغيرات، بس الأفضل لو الفرونت باعت product_id نفلتر بيها
    public function index()
    {
        if (request()->has('product_id')) {
            $variants = $this->variantService->getVariantsByProduct(request('product_id'));
        } else {
            $variants = ProductVariant::with('product')->get();
        }
        return $this->successResponse($variants, 'تم جلب المتغيرات بنجاح');
    }

    public function store(ProductVariantRequest $request)
    {
        $variant = $this->variantService->createVariant($request->validated());
        return $this->successResponse($variant, 'تم إضافة المتغير للمنتج بنجاح', 201);
    }

    public function show(ProductVariant $productVariant)
    {
        $productVariant->load('product');
        return $this->successResponse($productVariant, 'تفاصيل المتغير');
    }

    public function update(ProductVariantRequest $request, ProductVariant $productVariant)
    {
        $updatedVariant = $this->variantService->updateVariant($productVariant, $request->validated());
        return $this->successResponse($updatedVariant, 'تم تحديث المتغير بنجاح');
    }

    public function destroy(ProductVariant $productVariant)
    {
        $this->variantService->deleteVariant($productVariant);
        return $this->successResponse(null, 'تم حذف المتغير بنجاح');
    }
}
