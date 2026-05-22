<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Reel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ReelController extends Controller
{
    public function index()
    {
        return Reel::with('product')->latest()->get();
    }

    public function store(Request $request)
    {
        $request->validate([
            'video' => 'required|file|mimes:mp4,mov,webm',
            'product_id' => 'required|exists:products,id',
        ]);

        $video = $request->file('video')->store('reels', 'public');

        $reel = Reel::create([
            'video' => $video,
            'product_id' => $request->product_id,
        ]);

        return response()->json(
            $reel->load('product'),
            201
        );
    }

    public function show(Reel $reel)
    {
        return $reel->load('product');
    }

    public function update(Request $request, Reel $reel)
    {
        $request->validate([
            'video' => 'nullable|file|mimes:mp4,mov,webm',
            'product_id' => 'nullable|exists:products,id',
        ]);

        if ($request->hasFile('video')) {

            if ($reel->video) {
                Storage::disk('public')->delete($reel->video);
            }

            $reel->video = $request
                ->file('video')
                ->store('reels', 'public');
        }

        $reel->product_id = $request->product_id;

        $reel->save();

        return $reel->load('product');
    }

    public function destroy(Reel $reel)
    {
        if ($reel->video) {
            Storage::disk('public')->delete($reel->video);
        }

        $reel->delete();

        return response()->json([
            'message' => 'Reel deleted successfully',
        ]);
    }
}
