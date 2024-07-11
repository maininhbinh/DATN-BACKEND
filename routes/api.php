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
use App\Http\Controllers\SlideController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\api\ProductController;
use App\Http\Controllers\api\VariantController;
use App\Http\Controllers\api\VariantOptionController;
use App\Models\Variant_option;

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
    Route::get('list', [UserController::class, 'index']);
    Route::get('', [UserController::class, 'profile']);
    Route::post('', [UserController::class, 'store']);
    Route::get('{id}', [UserController::class, 'edit']);
    Route::post('/{id}', [UserController::class, 'update']);
    Route::delete('/{id}', [UserController::class, 'destroy']);
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
    Route::delete('/{id}', [DetailController::class, 'destroy']);
    Route::post('/{id}/restore', [DetailController::class, 'restore']);
});

Route::prefix('attribute')->group(function () {
    Route::get('', [AttributeController::class, 'index']);
    Route::post('', [AttributeController::class, 'store']);
    Route::get('/{id}', [AttributeController::class, 'edit']);
    Route::post('/{id}', [AttributeController::class, 'update']);
    Route::delete('/{id}', [AttributeController::class, 'destroy']);
    Route::post('/{id}/restore', [AttributeController::class, 'restore']);
});

Route::prefix('value')->group(function () {
    Route::get('', [ValueController::class, 'index']);
    Route::post('', [ValueController::class, 'store']);
    Route::get('/{id}', [ValueController::class, 'edit']);
    Route::post('/{id}', [ValueController::class, 'update']);
    Route::delete('/{id}', [ValueController::class, 'destroy']);
    Route::post('/{id}/restore', [ValueController::class, 'restore']);
});

Route::prefix('role')->group(function () {
    Route::get('', [RoleController::class, 'index']);
    Route::post('', [RoleController::class, 'store']);
    Route::get('/{id}', [RoleController::class, 'edit']);
    Route::post('/{id}', [RoleController::class, 'update']);
    Route::delete('/{id}', [RoleController::class, 'destroy']);
    Route::post('/{id}/restore', [RoleController::class, 'restore']);
});

Route::prefix('product')->group(function () {
    Route::get('', [ProductController::class, 'index']);
    Route::post('', [ProductController::class, 'store']);
    Route::get('/{slug}', [ProductController::class, 'show']);
    Route::get('/home/{feat}', [ProductController::class, 'featProducts']);
});


Route::prefix('slider')
    ->group(function () {

        Route::post('/', [SlideController::class, 'store']);
        Route::get('/', [SlideController::class, 'show']);
        Route::delete('/{id}', [SlideController::class, 'destroy']);
        Route::get('/{id}', [SlideController::class, 'edit']);
        Route::post('/{id}', [SlideController::class, 'update']);
    });


Route::prefix('variant')->group(function () {
    Route::get('', [VariantController::class, 'index']);
    Route::post('', [VariantController::class, 'store']);
    Route::get('/{id}', [VariantController::class, 'edit']);
    Route::post('/{id}', [VariantController::class, 'update']);
    Route::delete('/{id}', [VariantController::class, 'destroy']);
    Route::post('/{id}/restore', [VariantController::class, 'restore']);
});
Route::prefix('variant_option')->group(function () {
    Route::get('', [VariantOptionController::class, 'index']);
    Route::post('', [VariantOptionController::class, 'store']);
    Route::get('/{id}', [VariantOptionController::class, 'edit']);
    Route::post('/{id}', [VariantOptionController::class, 'update']);
    Route::delete('/{id}', [VariantOptionController::class, 'destroy']);
    Route::post('/{id}/restore', [VariantOptionController::class, 'restore']);
});



