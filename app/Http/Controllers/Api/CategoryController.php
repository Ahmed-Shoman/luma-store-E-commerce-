<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;
use App\Http\Resources\CategoryResource;

class CategoryController extends Controller
{
    // Cache TTL in seconds (60 min)
    private const TTL = 3600;

    /**
     * GET /api/categories
     * Returns all categories.
     */
    public function index(Request $request): JsonResponse
    {
        $categories = Category::all();

        return response()->json([
            'success' => true,
            'data' => $categories,
        ]);
    }

    /**
     * GET /api/categories/{id}
     * Single category — optionally includes its products.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $withProducts = $request->boolean('with_products', false);
        $cacheKey = "category_{$id}_" . ($withProducts ? 'products' : 'plain');

        $category = Cache::remember($cacheKey, self::TTL, function () use ($id, $withProducts) {
            $query = Category::select([
                'id',
                'name_en',
                'name_ar',
                'created_at',
            ]);

            if ($withProducts) {
                $query->with([
                    'products' => fn($q) => $q
                        ->select([
                            'id',
                            'name_ar',
                            'name_en',
                            'price',
                            'original_price',
                            'category_id',
                        ])
                        ->with([
                            'images:id,product_id,url,position',
                        ])
                        ->latest()
                        ->limit(20),
                ]);
            }

            return $query->find($id);
        });

        if (! $category) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new CategoryResource($category),
        ]);
    }

    /**
     * POST /api/categories
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name_en' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categories', 'name_en'),
            ],
            'name_ar' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categories', 'name_ar'),
            ],
        ]);

        $category = Category::create($validated);

        // Cache::tags([self::CACHE_TAG])->flush();

        return response()->json([
            'success' => true,
            'message' => 'Category created successfully.',
            'data' => new CategoryResource($category),
        ], 201);
    }

    /**
     * PUT/PATCH /api/categories/{id}
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $category = Category::find($id);

        if (! $category) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found.',
            ], 404);
        }

        $validated = $request->validate([
            'name_en' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('categories', 'name_en')->ignore($id),
            ],
            'name_ar' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('categories', 'name_ar')->ignore($id),
            ],
        ]);

        $category->update($validated);

        // Cache::tags([self::CACHE_TAG])->flush();

        return response()->json([
            'success' => true,
            'data' => new CategoryResource($category->fresh()),
        ]);
    }

    /**
     * DELETE /api/categories/{id}
     * Blocks deletion if products are still assigned.
     */
    public function destroy(int $id): JsonResponse
    {
        $category = Category::withCount('products')->find($id);

        if (! $category) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found.',
            ], 404);
        }

        if ($category->products_count > 0) {
            return response()->json([
                'success' => false,
                'message' => "Cannot delete: {$category->products_count} product(s) are still assigned to this category.",
            ], 422);
        }

        $category->delete();

        // Cache::tags([self::CACHE_TAG])->flush();

        return response()->json([
            'success' => true,
            'message' => 'Category deleted successfully.',
        ]);
    }
}
