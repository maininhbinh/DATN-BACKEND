<?php

use App\Http\Controllers\api\AttributeController;
use App\Http\Controllers\api\BrandController;
use App\Http\Controllers\api\CategoryController;
use App\Http\Controllers\api\AuthController;
use App\Http\Controllers\api\CartController;
use App\Http\Controllers\api\DetailController;
use App\Http\Controllers\api\RoleController;
use App\Http\Controllers\api\UserController;
use App\Http\Controllers\api\ValueController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\api\ProductController;

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

Route::prefix('auth')->group(function () {
    Route::post('signup', [AuthController::class, 'signup']);
    Route::post('verifyOTP', [AuthController::class, 'verifyOTP']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
});

Route::prefix('user')->group(function () {
    Route::get('', [UserController::class, 'profile'])->middleware('auth:sanctum');
    Route::post('', [UserController::class, 'store'])->middleware('auth:sanctum');
    Route::post('/{id}', [UserController::class, 'update'])->middleware('auth:sanctum');
    Route::post('/{id}', [UserController::class, 'destroy'])->middleware('auth:sanctum');
});

Route::middleware('auth:sanctum')->prefix('cart')->group(function () {
    Route::get('', [CartController::class, 'index']);
    Route::post('', [CartController::class, 'store']);
    Route::put('/{id}', [CartController::class, 'update']);
    Route::delete('/{id}', [CartController::class, 'destroy']);
});

Route::prefix('brand')->group(function () {
    Route::get('', [BrandController::class, 'index']);
    Route::post('', [BrandController::class, 'store']);
    Route::get('{id}', [BrandController::class, 'edit']);
    Route::post('{id}', [BrandController::class, 'update']);
    Route::delete('{id}', [BrandController::class, 'destroy']);
});

Route::prefix('category')->group(function () {
    Route::get('', [CategoryController::class, 'index']);
    Route::post('', [CategoryController::class, 'store']);
    Route::get('{id}', [CategoryController::class, 'edit']);
    Route::post('{id}', [CategoryController::class, 'update']);
    Route::delete('{id}', [CategoryController::class, 'destroy']);
});

Route::prefix('detail')->group(function () {
    Route::get('', [DetailController::class, 'index']);
    Route::post('', [DetailController::class, 'store']);
    Route::get('/{id}', [DetailController::class, 'edit']);
    Route::post('/{id}', [DetailController::class, 'update']);
    Route::delete('/{id}', [DetailController::class, 'delete']);
    Route::post('/{id}/restore', [DetailController::class, 'restore']);
});

Route::prefix('attribute')->group(function () {
    Route::get('', [AttributeController::class, 'index']);
    Route::post('', [AttributeController::class, 'store']);
    Route::get('/{id}', [AttributeController::class, 'edit']);
    Route::post('/{id}', [AttributeController::class, 'update']);
    Route::delete('/{id}', [AttributeController::class, 'delete']);
    Route::post('/{id}/restore', [AttributeController::class, 'restore']);
});

Route::prefix('value')->group(function () {
    Route::get('', [ValueController::class, 'index']);
    Route::post('', [ValueController::class, 'store']);
    Route::get('/{id}', [ValueController::class, 'edit']);
    Route::post('/{id}', [ValueController::class, 'update']);
    Route::delete('/{id}', [ValueController::class, 'delete']);
    Route::post('/{id}/restore', [ValueController::class, 'restore']);
});

Route::prefix('role')->group(function () {
    Route::get('', [RoleController::class, 'index']);
    Route::post('', [RoleController::class, 'store']);
    Route::get('/{id}', [RoleController::class, 'edit']);
    Route::post('/{id}', [RoleController::class, 'update']);
    Route::delete('/{id}', [RoleController::class, 'delete']);
    Route::post('/{id}/restore', [RoleController::class, 'restore']);
});

Route::prefix('product')->group(function () {
    Route::get('', [ProductController::class, 'index']);
    Route::post('', [ProductController::class, 'store']);
});

