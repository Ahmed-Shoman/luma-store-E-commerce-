<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Http\Resources\ProductResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class ProductController extends Controller
{
    /**
     * GET /api/products
     */



    public function index(Request $request): JsonResponse
    {
        $cacheKey = 'products_' . md5(json_encode($request->all()));

        $products = Cache::tags(['products'])->remember($cacheKey, 60, function () use ($request) {

            $query = Product::query()
                ->select([
                    'id',
                    'name_ar',
                    'name_en',
                    'price',
                    'original_price',
                    'category_id',
                    'is_best_seller',
                    'is_new_arrival',
                    'is_trending',
                    'created_at',
                ])
                ->with([
                    'category:id,name_en,name_ar',
                    'images:id,product_id,url,position'
                ]);

            if ($request->category) {
                $query->where('category_id', $request->category);
            }

            if ($request->boolean('is_best_seller')) {
                $query->where('is_best_seller', true);
            }

            if ($request->boolean('is_new_arrival')) {
                $query->where('is_new_arrival', true);
            }

            if ($request->boolean('is_trending')) {
                $query->where('is_trending', true);
            }

            if ($request->search) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name_en', 'like', "%{$search}%")
                        ->orWhere('name_ar', 'like', "%{$search}%");
                });
            }

            $sortBy = in_array($request->sort_by, ['price', 'created_at', 'name_en', 'name_ar'])
                ? $request->sort_by
                : 'created_at';

            $query->orderBy($sortBy, $request->sort_order === 'asc' ? 'asc' : 'desc');

            $perPage = min((int) $request->per_page, 20);

            return $query->paginate($perPage);
        });

        return response()->json([
            'success' => true,
            'data' => $products,
        ]);
    }

    /**
     * GET /api/products/{id}
     */
    public function show(int $id): JsonResponse
    {
        $product = Product::with([
            'images',
            'reels',
            'category'
        ])->find($id);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new ProductResource($product),
        ]);
    }

    /**
     * POST /api/products
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name_ar' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
            'description_ar' => 'nullable|string',
            'description_en' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'original_price' => 'nullable|numeric|min:0|gt:price',

            'category_id' => 'required|exists:categories,id',

            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,jpg,png,webp',

            'is_best_seller' => 'boolean',
            'is_new_arrival' => 'boolean',
            'is_trending' => 'boolean',
        ]);

        $product = Product::create($validated);

        Cache::tags(['products'])->flush();

        // store images (separate table recommended)
        foreach ($request->file('images', []) as $file) {
            $path = $file->store('products', 'public');

            $product->images()->create([
                'url' => asset('storage/' . $path),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Product created successfully.',
            'data' => new ProductResource($product->load('images')),
        ], 201);
    }

    /**
     * PUT/PATCH /api/products/{id}
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json(['success' => false], 404);
        }

        $validated = $request->validate([
            'name_ar' => 'sometimes|string|max:255',
            'name_en' => 'sometimes|string|max:255',
            'price' => 'sometimes|numeric|min:0',
            'original_price' => 'nullable|numeric|min:0|gt:price',
            'category_id' => 'sometimes|exists:categories,id',
            'is_best_seller' => 'boolean',
            'is_new_arrival' => 'boolean',
            'is_trending' => 'boolean',
        ]);

        $product->update($validated);

        Cache::tags(['products'])->flush();

        return response()->json([
            'success' => true,
            'data' => new ProductResource($product->fresh()),
        ]);
    }

    /**
     * DELETE /api/products/{id}
     */
    public function destroy(int $id): JsonResponse
    {
        $product = Product::find($id);



        if (!$product) {
            return response()->json(['success' => false], 404);
        }

        $product->images()->delete();
        $product->reels()->delete();
        $product->delete();

        Cache::tags(['products'])->flush();

        return response()->json([
            'success' => true,
            'message' => 'Deleted successfully',
        ]);
    }
}
