<?php

use App\Http\Controllers\api\BrandController;
use App\Http\Controllers\api\CategoriesController;
use App\Http\Controllers\api\CategoryAttributeController;
use App\Http\Controllers\api\CategoryController;
use App\Http\Controllers\api\ParametersController;
use App\Http\Controllers\api\ProductController;
use App\Http\Controllers\api\ValueAttributeController;
use App\Http\Controllers\api\AuthController;
use App\Http\Controllers\api\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::prefix('auth')
->group(function (){

    Route::post('signup', [AuthController::class, 'signup']);
    Route::post('verifyOTP', [AuthController::class, 'verifyOTP']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

});

Route::prefix('user')
->group(function (){

    Route::get('profile', [UserController::class, 'profile'])->middleware('auth:sanctum');

});

Route::prefix('brand')
    ->group(function () {
    Route::post('create', [BrandController::class, 'store']);
});

Route::prefix('category')
    ->group(function () {
        Route::get('', [CategoryController::class, 'index']);
        Route::post('create', [CategoryController::class, 'store']);
        Route::get('{id}', [CategoryController::class, 'edit']);
    });

Route::prefix('product')
    ->group(function () {
//        Route::get('', [ProductController::class, 'index']);
        Route::post('create', [ProductController::class, 'create']);
    });

