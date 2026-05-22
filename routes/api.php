<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ReelController;



// this route gives us all abilities to manage everything in our products     #Abdalazeem
Route::apiResource('products', ProductController::class);

// this route give us all abilities to manage everything in our Reels and their products     #Abdalazeem
Route::apiResource('reels', ReelController::class);
