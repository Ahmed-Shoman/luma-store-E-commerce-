<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Http\Resources\ProductResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use App\Services\ImageService;

class ProductController extends Controller
{
    /**
     * GET /api/products
     */
    private ImageService $imageService;

    public function __construct(ImageService $imageService)
    {
        $this->imageService = $imageService;
    }


    public function index(Request $request): JsonResponse
    {
        $query = Product::query()
            ->select([
                'id',
                'name_ar',
                'name_en',
                'description_ar',
                'description_en',
                'price',
                'original_price',
                'category_id',
                'is_best_seller',
                'is_new_arrival',
                'is_trending',
                'reel_video',
                'created_at',
            ])
            ->with([
                'category:id,name_en,name_ar',
                'images:id,product_id,url',
            ]);

        if ($request->filled('category')) {
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

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('name_en', 'like', "%{$search}%")
                    ->orWhere('name_ar', 'like', "%{$search}%");
            });
        }

        $sortBy = in_array(
            $request->sort_by,
            ['price', 'created_at', 'name_en', 'name_ar']
        ) ? $request->sort_by : 'created_at';

        $query->orderBy(
            $sortBy,
            $request->sort_order === 'asc' ? 'asc' : 'desc'
        );

        $products = $query->get();

        return response()->json([
            'success' => true,
            'data' => $products,
        ]);
    }

    /**
     * GET /api/admin/products
     */
    public function adminIndex(Request $request): JsonResponse
    {
        $products = Product::with([
            'category:id,name_en,name_ar',
            'images:id,product_id,url'
        ])->orderBy('created_at', 'desc')->get();

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
            'images.*' => 'file|mimes:jpeg,jpg,png,webp,avif,heic,heif|max:10240',

            'is_best_seller' => 'boolean',
            'is_new_arrival' => 'boolean',
            'is_trending' => 'boolean',

            'reel_video' => 'nullable|file|mimes:mp4,mov,ogg,qt|max:50000',
        ]);

        $product = Product::create(
            collect($validated)
                ->except(['images', 'reel_video'])
                ->toArray()
        );

        // Upload Images
        foreach ($request->file('images', []) as $file) {

            $imageData = $this->imageService->uploadProductImage($file);

            $product->images()->create([
                'url' => $imageData['url'],
                'thumbnail_url' => $imageData['thumbnail_url'],
            ]);
        }

        // Upload Reel Video
        if ($request->hasFile('reel_video')) {

            $videoPath = $request
                ->file('reel_video')
                ->store('reels', 'public');

            $product->update([
                'reel_video' => $videoPath,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Product created successfully.',
            'data' => new ProductResource(
                $product->fresh()->load([
                    'images',
                    'category'
                ])
            ),
        ], 201);
    }

    /**
     * PUT/PATCH /api/products/{id}
     */
    /**
     * POST /api/products/{id}  (with _method=PATCH for multipart)
     * PATCH /api/products/{id}
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found.',
            ], 404);
        }

        $validated = $request->validate([
            'name_ar'        => 'sometimes|string|max:255',
            'name_en'        => 'sometimes|string|max:255',
            'description_ar' => 'nullable|string',
            'description_en' => 'nullable|string',
            'price'          => 'sometimes|numeric|min:0',
            'original_price' => 'nullable|numeric|min:0|gt:price',
            'category_id'    => 'sometimes|exists:categories,id',

            'is_best_seller' => 'boolean',
            'is_new_arrival' => 'boolean',
            'is_trending'    => 'boolean',

            'reel_video' => 'nullable|file|mimes:mp4,mov,ogg,qt|max:50000',

            'images' => 'nullable|array',
            'images.*' => 'file|mimes:jpeg,jpg,png,webp,avif,heic,heif|max:10240',

            'keep_images' => 'nullable|array',
            'keep_images.*' => 'nullable|string',
        ]);

        // Update Product Fields
        $product->update(
            collect($validated)
                ->except([
                    'images',
                    'keep_images',
                    'reel_video'
                ])
                ->toArray()
        );

        // Update Reel Video
        if ($request->hasFile('reel_video')) {

            if ($product->reel_video) {
                Storage::disk('public')->delete($product->reel_video);
            }

            $videoPath = $request
                ->file('reel_video')
                ->store('reels', 'public');

            $product->update([
                'reel_video' => $videoPath,
            ]);
        } elseif ($request->input('remove_reel_video') === 'true') {

            if ($product->reel_video) {

                Storage::disk('public')->delete(
                    $product->reel_video
                );

                $product->update([
                    'reel_video' => null,
                ]);
            }
        }

        // Handle Images
        $hasNewImages = $request->hasFile('images');
        $hasKeepList = $request->has('keep_images');

        if ($hasNewImages || $hasKeepList) {

            // Delete Removed Images
            if ($hasKeepList) {

                $keepUrls = collect(
                    $request->input('keep_images', [])
                )
                    ->filter()
                    ->values();

                foreach (
                    $product->images()
                        ->whereNotIn('url', $keepUrls)
                        ->get() as $img
                ) {

                    $this->imageService->deleteImage(
                        $img->url
                    );

                    $img->delete();
                }
            }

            // Upload New Images
            foreach ($request->file('images', []) as $file) {

                $imageData = $this->imageService->uploadProductImage($file);

                $product->images()->create([
                    'url' => $imageData['url'],
                    'thumbnail_url' => $imageData['thumbnail_url'],
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Product updated successfully.',
            'data' => new ProductResource(
                $product->fresh()->load([
                    'images',
                    'category'
                ])
            ),
        ]);
    }


    /**
     * DELETE /api/products/{id}
     */
    public function destroy(int $id): JsonResponse
    {
        $product = Product::with('images')->find($id);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found.',
            ], 404);
        }

        // Delete Reel Video
        if ($product->reel_video) {
            Storage::disk('public')->delete(
                $product->reel_video
            );
        }

        // Delete Product Images From Storage
        foreach ($product->images as $image) {

            $this->imageService->deleteImage(
                $image->url
            );
        }

        // Delete Image Records
        $product->images()->delete();

        // Delete Product
        $product->delete();

        return response()->json([
            'success' => true,
            'message' => 'Deleted successfully',
        ]);
    }
}
