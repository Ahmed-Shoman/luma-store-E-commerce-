<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProductController;




// this route gives us all abilities to manage everything in our products     #Abdalazeem
Route::apiResource('products', ProductController::class);
