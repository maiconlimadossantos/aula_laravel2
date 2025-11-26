<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
Route::apiResource("items", App\Http\Controllers\ItemController::class);
Route::apiResource("users", App\Http\Controllers\UserController::class);