<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ReelController;
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

// ── Reels ─────────────────────────────────────────────────────
Route::prefix('reels')->name('reels.')->group(function () {
    Route::get('/',          [ReelController::class, 'index'])->name('index');
    Route::get('/{reel}',    [ReelController::class, 'show'])->name('show');
    Route::post('/',         [ReelController::class, 'store'])->name('store');
    Route::post('/{reel}',   [ReelController::class, 'update'])->name('update');
    Route::patch('/{reel}',  [ReelController::class, 'update'])->name('update-patch');
    Route::delete('/{reel}', [ReelController::class, 'destroy'])->name('destroy');
});


// ── Orders ─────────────────────────────────────────────────────
Route::prefix('orders')->group(function () {
    Route::get('/', [OrderController::class, 'index']);
    Route::get('/{id}', [OrderController::class, 'show']);
    Route::post('/',         [OrderController::class, 'store']);
    Route::patch('/{id}/status', [OrderController::class, 'updateStatus']);
    Route::delete('/{id}', [OrderController::class, 'destroy']);
});
