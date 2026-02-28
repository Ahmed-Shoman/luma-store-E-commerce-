<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CategoryRequest;
use App\Models\Category;
use App\Services\CategoryService;

class CategoryController extends Controller
{
    protected $categoryService;

    public function __construct(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }

    public function index()
    {
        $categories = $this->categoryService->getAllForAdmin();
        return $this->successResponse($categories, 'تم جلب الأقسام بنجاح');
    }

    public function store(CategoryRequest $request)
    {
        $category = $this->categoryService->createCategory($request->validated());
        return $this->successResponse($category, 'تم إنشاء القسم بنجاح', 201);
    }

    public function show(Category $category)
    {
        return $this->successResponse($category, 'تفاصيل القسم');
    }

    public function update(CategoryRequest $request, Category $category)
    {
        $updatedCategory = $this->categoryService->updateCategory($category, $request->validated());
        return $this->successResponse($updatedCategory, 'تم تحديث القسم بنجاح');
    }

    public function destroy(Category $category)
    {
        $this->categoryService->deleteCategory($category);
        return $this->successResponse(null, 'تم حذف القسم بنجاح');
    }
}