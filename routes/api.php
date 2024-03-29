<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group([
    'prefix' => 'auth'
],function(){
    Route::post('login',[\App\Http\Controllers\AuthController::class,'login']);
    Route::post('register',[\App\Http\Controllers\AuthController::class,'register']);
});

Route::group([
    'middleware' => ['auth:api']
],function(){
    Route::post('/logout', [\App\Http\Controllers\AuthController::class,'logout']);
    Route::post('/authenticate', [\App\Http\Controllers\AuthController::class,'authenticate']);
    Route::resource('product',\App\Http\Controllers\Api\ProductController::class);
    Route::resource('order',\App\Http\Controllers\Api\OrdersController::class);
    Route::get('/customer', [\App\Http\Controllers\Api\OrdersController::class,'customerReport']);

    Route::post('/discount', [\App\Http\Controllers\Api\DiscountController::class,'calculateDiscounts']);

});
