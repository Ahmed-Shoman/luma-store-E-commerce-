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

use App\Http\Controllers\Api\DashboardController;

// ── Dashboard ─────────────────────────────────────────────────
Route::prefix('dashboard')->group(function () {
    Route::get('/overview', [DashboardController::class, 'overview']);
    Route::get('/revenue', [DashboardController::class, 'revenue']);
    Route::get('/order-status', [DashboardController::class, 'orderStatus']);
    Route::get('/top-products', [DashboardController::class, 'topProducts']);
    Route::get('/categories', [DashboardController::class, 'categories']);
    Route::get('/latest-orders', [DashboardController::class, 'latestOrders']);
    Route::get('/top-customers', [DashboardController::class, 'topCustomers']);
    Route::get('/activity', [DashboardController::class, 'activity']);
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
