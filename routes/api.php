<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\Auth\AuthController;

use App\Http\Controllers\Api\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Api\Public\ProductController as PublicProductController;

use App\Http\Controllers\Api\Public\CategoryController;

use App\Http\Controllers\Api\Admin\ProductImageController;
use App\Http\Controllers\Api\Admin\InventoryController;

use App\Http\Controllers\Api\Customer\CartController;
use App\Http\Controllers\Api\Customer\CheckoutController;
use App\Http\Controllers\Api\Customer\OrderController as CustomerOrderController;

use App\Http\Controllers\Api\Admin\OrderController as AdminOrderController;

use App\Http\Controllers\Api\Admin\DashboardController;

use App\Http\Controllers\Api\Customer\ProfileController;
use App\Http\Controllers\Api\Customer\AddressController;

use App\Http\Controllers\Api\Payment\HitPayController;

/*
|--------------------------------------------------------------------------
| Authentication
|--------------------------------------------------------------------------
*/

Route::prefix('auth')->group(function () {

    Route::post('/login', [AuthController::class, 'login']);

    Route::post('/register', [AuthController::class, 'register']);

    Route::middleware('auth:sanctum')->group(function () {

        Route::get('/me', [AuthController::class, 'me']);

        Route::post('/logout', [AuthController::class, 'logout']);

    });

});

/*
|--------------------------------------------------------------------------
| Public
|--------------------------------------------------------------------------
*/

Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/categories/{category:slug}', [CategoryController::class, 'show']);

/*
|--------------------------------------------------------------------------
| Public Products
|--------------------------------------------------------------------------
*/

Route::get('/products/search', [PublicProductController::class, 'search']);

Route::get('/products', [PublicProductController::class, 'index']);

Route::get('/products/category/{category}', [PublicProductController::class, 'category']);

Route::get('/products/{slug}', [PublicProductController::class, 'show']);

Route::get('/products/{slug}/related', [PublicProductController::class, 'related']);

/*
|--------------------------------------------------------------------------
| Admin
|--------------------------------------------------------------------------
*/

Route::prefix('admin')
    ->middleware('auth:sanctum')
    ->group(function () {

        Route::get(

            'dashboard',

            [DashboardController::class,'index']

        );

        Route::apiResource(
            'products',
            AdminProductController::class
        );

        Route::get(
            'products/{product}/images',
            [ProductImageController::class, 'index']
        );

        Route::post(
            'products/{product}/images',
            [ProductImageController::class, 'store']
        );

        Route::delete(
            'products/images/{image}',
            [ProductImageController::class, 'destroy']
        );

        Route::patch(
            'products/images/{image}/primary',
            [ProductImageController::class, 'setPrimary']
        );

        Route::patch(
            'products/{product}/images/reorder',
            [ProductImageController::class, 'reorder']
        );

        Route::get(
            'products/{product}/inventory',
            [InventoryController::class, 'history']
        );

        Route::post(
            'products/{product}/inventory',
            [InventoryController::class, 'store']
        );

        Route::get(
            'orders',
            [AdminOrderController::class, 'index']
        );

        Route::get(
            'orders/{order}',
            [AdminOrderController::class, 'show']
        );

        Route::patch(
            'orders/{order}/status',
            [AdminOrderController::class, 'updateStatus']
        );

    });

/*
|--------------------------------------------------------------------------
| Profile
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {

    Route::get(

        '/profile',

        [ProfileController::class,'show']

    );

    Route::patch(

        '/profile',

        [ProfileController::class,'update']

    );

    Route::get('/addresses', [AddressController::class, 'index']);
    Route::post('/addresses', [AddressController::class, 'store']);
    Route::patch('/addresses/{address}', [AddressController::class, 'update']);
    Route::delete('/addresses/{address}', [AddressController::class, 'destroy']);
    Route::patch('/addresses/{address}/default', [AddressController::class, 'setDefault']);

});

/*
|--------------------------------------------------------------------------
| Customer
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')
    ->group(function () {

        /*
        |--------------------------------------------------------------------------
        | Cart
        |--------------------------------------------------------------------------
        */

        Route::prefix('cart')->group(function () {

            Route::get('/', [CartController::class, 'index']);

            Route::post('/', [CartController::class, 'store']);

            Route::patch('/{cartItem}', [CartController::class, 'update']);

            Route::delete('/{cartItem}', [CartController::class, 'destroy']);

            Route::delete('/', [CartController::class, 'clear']);

        });

        /*
        |--------------------------------------------------------------------------
        | Checkout
        |--------------------------------------------------------------------------
        */

        Route::post(
            '/checkout',
            [CheckoutController::class, 'store']
        );

        /*
        |--------------------------------------------------------------------------
        | Customer Orders
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/orders',
            [CustomerOrderController::class, 'index']
        );

        Route::get(
            '/orders/{order}',
            [CustomerOrderController::class, 'show']
        );

    });


/*
|--------------------------------------------------------------------------
| HitPay
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {

    Route::post(
        '/payments/hitpay',
        [HitPayController::class, 'create']
    );

});

Route::post(
    '/payments/webhook',
    [HitPayController::class, 'webhook']
);

Route::get(
    '/payments/callback',
    [HitPayController::class, 'callback']
);
