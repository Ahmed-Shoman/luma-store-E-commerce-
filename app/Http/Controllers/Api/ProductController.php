<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Http\Resources\ProductResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class ProductController extends Controller
{
    /**
     * GET /api/products
     */
    public function index(Request $request): JsonResponse
    {
        $query = Product::query();

        if ($request->has('category'))          $query->byCategory($request->category);
        if ($request->boolean('is_best_seller')) $query->bestSellers();
        if ($request->boolean('is_new_arrival')) $query->newArrivals();
        if ($request->boolean('is_trending'))    $query->trending();
        if ($request->boolean('on_sale'))        $query->onSale();

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name_ar', 'like', "%{$search}%")
                    ->orWhere('name_en', 'like', "%{$search}%");
            });
        }

        $sortBy       = $request->get('sort_by', 'created_at');
        $sortOrder    = $request->get('sort_order', 'desc');
        $allowedSorts = ['price', 'created_at', 'name_en', 'name_ar'];

        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortOrder === 'asc' ? 'asc' : 'desc');
        }

        $perPage  = (int) $request->get('per_page', 15);
        $products = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data'    => ProductResource::collection($products),
        ]);
    }

    /**
     * GET /api/products/{id}
     */
    public function show(int $id): JsonResponse
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json(['success' => false, 'message' => 'Product not found.'], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => new ProductResource($product),
        ]);
    }

    /**
     * POST /api/products
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name_ar'        => 'required|string|max:255',
                'name_en'        => 'required|string|max:255',
                'description_ar' => 'nullable|string',
                'description_en' => 'nullable|string',
                'price'          => 'required|numeric|min:0',
                'original_price' => 'nullable|numeric|min:0|gt:price',
                'images'         => 'nullable|array',
                'images.*'       => 'required|image|mimes:jpeg,jpg,png,webp|max:5120',
                'category'       => 'required|string|max:255',
                'is_best_seller' => 'boolean',
                'is_new_arrival' => 'boolean',
                'is_trending'    => 'boolean',
            ]);

            $validated['images'] = $this->storeImages($request);

            $product = Product::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Product created successfully.',
                'data'    => new ProductResource($product),
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors'  => $e->errors(),
            ], 422);
        }
    }

    /**
     * PUT/PATCH /api/products/{id}
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json(['success' => false, 'message' => 'Product not found.'], 404);
        }

        try {
            $validated = $request->validate([
                'name_ar'        => 'sometimes|required|string|max:255',
                'name_en'        => 'sometimes|required|string|max:255',
                'description_ar' => 'nullable|string',
                'description_en' => 'nullable|string',
                'price'          => 'sometimes|required|numeric|min:0',
                'original_price' => 'nullable|numeric|min:0|gt:price',
                'images'         => 'nullable|array',
                'images.*'       => 'image|mimes:jpeg,jpg,png,webp|max:5120',
                'category'       => 'sometimes|required|string|max:255',
                'is_best_seller' => 'boolean',
                'is_new_arrival' => 'boolean',
                'is_trending'    => 'boolean',
            ]);

            // If new images uploaded → delete old ones and store new
            if ($request->hasFile('images')) {
                $product->deleteImages();
                $validated['images'] = $this->storeImages($request);
            }

            $product->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Product updated successfully.',
                'data'    => new ProductResource($product->fresh()),
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors'  => $e->errors(),
            ], 422);
        }
    }

    /**
     * DELETE /api/products/{id}
     */
    public function destroy(int $id): JsonResponse
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json(['success' => false, 'message' => 'Product not found.'], 404);
        }

        // Delete image files from storage before removing the record
        $product->deleteImages();
        $product->delete();

        return response()->json([
            'success' => true,
            'message' => 'Product deleted successfully.',
        ]);
    }

    // ─── Private Helper ───────────────────────────────────────────────

    /**
     * Store uploaded image files and return array of public URLs.
     */
    private function storeImages(Request $request): array
    {
        $urls = [];

        foreach ($request->file('images', []) as $file) {
            $path  = $file->store('products', 'public');
            $urls[] = Storage::disk('public')->url($path);
        }

        return $urls;
    }
}
