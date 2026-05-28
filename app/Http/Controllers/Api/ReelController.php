<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ReelResource;
use App\Models\Reel;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class ReelController extends Controller
{

    /**
     * GET /api/reels
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = min((int) $request->input('per_page', 12), 24);

        $query = Reel::query()
            ->select(['id', 'video', 'product_id', 'created_at'])
            ->with([
                'product:id,name_en,name_ar',
                'product.images:id,product_id,url,position',
            ])
            ->latest();

        // Optional filter by product
        if ($request->filled('product_id')) {
            $query->where('product_id', $request->integer('product_id'));
        }

        $reels = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data'    => ReelResource::collection($reels)->response()->getData(true),
        ]);
    }

    /**
     * GET /api/reels/{reel}
     */
    public function show(Reel $reel): JsonResponse
    {
        $reel->load([
            'product:id,name_en,name_ar',
            'product.images:id,product_id,url,position',
        ]);

        return response()->json([
            'success' => true,
            'data'    => new ReelResource($reel),
        ]);
    }

    /**
     * POST /api/reels
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'video'      => 'required|file|mimes:mp4,mov,webm|max:102400', // 100 MB
            'product_id' => 'required|exists:products,id',
        ]);

        $path = $request->file('video')->store('reels', 'public');

        $reel = Reel::create([
            'video'      => $path,
            'product_id' => $validated['product_id'],
        ]);


        return response()->json([
            'success' => true,
            'message' => 'Reel uploaded successfully.',
            'data'    => new ReelResource($reel->load([
                'product:id,name_en,name_ar',
                'product.images:id,product_id,url,position',
            ])),
        ], 201);
    }

    /**
     * POST /api/reels/{reel}   (use POST + _method=PATCH for multipart)
     * PATCH /api/reels/{reel}
     */
    public function update(Request $request, Reel $reel): JsonResponse
    {
        $validated = $request->validate([
            'video'      => 'nullable|file|mimes:mp4,mov,webm|max:102400',
            'product_id' => 'nullable|exists:products,id',
        ]);

        if ($request->hasFile('video')) {
            // Delete old file before storing new one
            Storage::disk('public')->delete($reel->video);
            $validated['video'] = $request->file('video')->store('reels', 'public');
        }

        $reel->update(array_filter($validated, fn($v) => ! is_null($v)));


        return response()->json([
            'success' => true,
            'data'    => new ReelResource($reel->fresh()->load([
                'product:id,name_en,name_ar',
                'product.images:id,product_id,url,position',
            ])),
        ]);
    }

    /**
     * DELETE /api/reels/{reel}
     */
    public function destroy(Reel $reel): JsonResponse
    {
        Storage::disk('public')->delete($reel->video);

        $reel->delete();


        return response()->json([
            'success' => true,
            'message' => 'Reel deleted successfully.',
        ]);
    }
}
