<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\AddressController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\AdminOrderController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register'])
        ->middleware('throttle:6,1');

    Route::post('login', [AuthController::class, 'login'])
        ->middleware('throttle:6,1');

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('me', [AuthController::class, 'me']);
        Route::post('logout', [AuthController::class, 'logout']);
    });
});

Route::middleware('auth:sanctum')->prefix('addresses')->group(function () {
    Route::get('/', [AddressController::class, 'index']);
    Route::post('/', [AddressController::class, 'store']);
    Route::patch('/{addressId}/default', [AddressController::class, 'setDefault']);
});
        
Route::get('categories', [CategoryController::class, 'index']);

Route::get('products',[ProductController::class, 'index']);
Route::get('products/{id}', [ProductController::class, 'show'])->whereNumber('id');

Route::get('/cart', [CartController::class, 'show']);

Route::post('/cart/items', [CartController::class, 'store']);

Route::put('/cart/items/{productId}', [CartController::class, 'update']);

Route::delete('/cart/items/{productId}', [CartController::class, 'destroy']);

Route::middleware('auth:sanctum')->prefix('orders')->group(function () {
    Route::post('/', [OrderController::class, 'store']);
    Route::get('/', [OrderController::class, 'index']);
    Route::get('/{id}', [OrderController::class, 'show'])->whereNumber('id');
    Route::post('/{id}/pay', [OrderController::class, 'pay'])->whereNumber('id');
    Route::post('/{id}/cancel', [OrderController::class, 'cancel'])->whereNumber('id');
});

   Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {
    Route::get('/products', [ProductController::class, 'adminIndex']);
    Route::get('/orders', [AdminOrderController::class, 'index']);
    Route::patch('/orders/{id}/status', [AdminOrderController::class, 'updateStatus'])->whereNumber('id');
    Route::post('/categories', [CategoryController::class, 'store']);
    Route::put('/categories/{id}', [CategoryController::class, 'update'])->whereNumber('id');
    Route::delete('/categories/{id}', [CategoryController::class, 'destroy'])->whereNumber('id');
    Route::post('/products', [ProductController::class, 'store']);
    Route::put('/products/{id}', [ProductController::class, 'update'])->whereNumber('id');
    Route::delete('/products/{id}', [ProductController::class, 'destroy'])->whereNumber('id');
});

