<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\SettingController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ProductVariantController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\CustomerController;




Route::post('/auth/login', [AuthController::class, 'login']);


Route::middleware('auth:sanctum')->group(function () {

    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/profile', [AuthController::class, 'profile']);

    Route::get('/settings', [SettingController::class, 'index']);
    Route::post('/settings/update', [SettingController::class, 'update']);

    Route::apiResource('categories', CategoryController::class);

    Route::apiResource('products', ProductController::class);

    Route::apiResource('product-variants', ProductVariantController::class);


Route::apiResource('orders', OrderController::class)->except(['update', 'destroy']);
Route::patch('orders/{order}/status', [OrderController::class, 'updateStatus']);

Route::apiResource('customers', CustomerController::class);
    Route::apiResource('banners', \App\Http\Controllers\Api\BannerController::class);


});
Route::get('/active-banner', [\App\Http\Controllers\Api\BannerController::class, 'getActiveBanner']);