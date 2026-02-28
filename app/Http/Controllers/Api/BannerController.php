<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BannerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function getActiveBanner()
    {
        $banner = Banner::where('is_active', true)
            ->where(function ($query) {
                // البانر شغال لو ملوش تاريخ انتهاء، أو تاريخ انتهائه لسه في المستقبل
                $query->whereNull('end_date')
                      ->orWhere('end_date', '>', now());
            })
            ->where(function ($query) {
                // وتاريخ بدايته عدى أو ملوش تاريخ بداية
                $query->whereNull('start_date')
                      ->orWhere('start_date', '<=', now());
            })
            ->latest()
            ->first(); // بنجيب أحدث بانر واحد بس لعرضه

        return $this->successResponse($banner, 'Active Banner');
    }
}
