<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\OrderController;


// ── Categories ────────────────────────────────────────────────
Route::prefix('categories')->group(function () {
    Route::get('/',        [CategoryController::class, 'index']);
    Route::get('/{id}',    [CategoryController::class, 'show']);
    Route::post('/',       [CategoryController::class, 'store']);
    Route::patch('/{id}',  [CategoryController::class, 'update']);
    Route::delete('/{id}', [CategoryController::class, 'destroy']);
});

// ── Products ──────────────────────────────────────────────────
Route::prefix('products')->group(function () {
    Route::get('/',        [ProductController::class, 'index']);
    Route::get('/{id}',    [ProductController::class, 'show']);
    Route::post('/',       [ProductController::class, 'store']);
    Route::patch('/{id}',  [ProductController::class, 'update']);
    Route::delete('/{id}', [ProductController::class, 'destroy']);
});

// Admin Products
Route::get('admin/products', [ProductController::class, 'adminIndex']);


// ── Orders ─────────────────────────────────────────────────────
Route::prefix('orders')->group(function () {
    Route::get('/', [OrderController::class, 'index']);
    Route::get('/{id}', [OrderController::class, 'show']);
    Route::post('/',         [OrderController::class, 'store']);
    Route::patch('/{id}/status', [OrderController::class, 'updateStatus']);
    Route::delete('/{id}', [OrderController::class, 'destroy']);
});
