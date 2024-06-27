<?php

use App\Http\Controllers\api\CategoriesController;
use App\Http\Controllers\api\CategoryAttributeController;
use App\Http\Controllers\api\ParametersController;
use App\Http\Controllers\api\ValueAttributeController;
use App\Http\Controllers\api\AuthController;
use App\Http\Controllers\api\IntroduceController;
use App\Http\Controllers\api\UserController;
use App\Http\Controllers\SlideController;
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
    ->group(function () {

        Route::post('signup', [AuthController::class, 'signup']);
        Route::post('verifyOTP', [AuthController::class, 'verifyOTP']);
        Route::post('login', [AuthController::class, 'login']);
        Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
    });

Route::prefix('user')
    ->group(function () {

        Route::get('profile', [UserController::class, 'profile'])->middleware('auth:sanctum');
    });


Route::prefix('category')
    ->group(function () {
        // lớp cha
        Route::get('/', [CategoriesController::class, 'index']);
        Route::post('/', [CategoriesController::class, 'store']);
        //update lớp cha (category)
        Route::post('/{id}', [CategoriesController::class, 'update']);
        // lớp con
        Route::get('/{id}', [CategoriesController::class, 'show']); // show lớp con
        Route::post('/child', [CategoriesController::class, 'storeChild']); //thêm lớp con
        Route::post('/{id}/children/{child_id}', [CategoriesController::class, 'updateChild']);
        //xoá lớp cha(đã kiểm tra sự tồn tại nếu có lớp con)
        Route::delete('/{id}', [CategoriesController::class, 'destroy']);
        Route::post('/{id}/restore', [CategoriesController::class, 'restore']);

    });

// parameter
Route::prefix('parameter')
    ->group(function () {
        Route::get('/', [ParametersController::class, 'index']);
        Route::post('/', [ParametersController::class, 'store']);
        Route::get('/{id}', [ParametersController::class, 'show']);
        Route::post('/{id}', [ParametersController::class, 'update']);
        Route::delete('/{id}', [ParametersController::class, 'destroy']);
        Route::post('/{id}/restore', [ParametersController::class, 'restore']);

    });
Route::prefix('value-attribute')
    ->group(function () {
        Route::get('/', [ValueAttributeController::class, 'index']);
        Route::post('/', [ValueAttributeController::class, 'store']);
        Route::get('/{id}', [ValueAttributeController::class, 'show']);
        Route::post('/{id}', [ValueAttributeController::class, 'update']);
        Route::delete('/{id}', [ValueAttributeController::class, 'destroy']);
        Route::post('/{id}/restore', [ValueAttributeController::class, 'restore']);
    });
    Route::prefix('introduce')
    ->group(function () {
        Route::get('/', [IntroduceController::class, 'index']);
        Route::post('/', [IntroduceController::class, 'store']);
        Route::get('/{id}', [IntroduceController::class, 'show']);
        Route::post('/{id}', [IntroduceController::class, 'update']);
        Route::delete('/{id}', [IntroduceController::class, 'destroy']);
        Route::post('/{id}', [IntroduceController::class, 'destroy']);
        Route::post('/{id}/restore', [IntroduceController::class, 'restore']);
    });
Route::prefix('category-attribute')
    ->group(function () {
        Route::get('/', [CategoryAttributeController::class, 'index']);
        Route::post('/', [CategoryAttributeController::class, 'store']);
        Route::get('/{id}', [CategoryAttributeController::class, 'show']);
        Route::post('/{id}', [CategoryAttributeController::class, 'update']);
        Route::delete('/{id}', [CategoryAttributeController::class, 'destroy']);
        Route::post('/{id}', [CategoryAttributeController::class, 'destroy']);
        Route::post('/{id}/restore', [CategoryAttributeController::class, 'restore']);
    });


Route::prefix('slider')
    ->group(function () {

        Route::post('/', [SlideController::class, 'store']);
        Route::get('/', [SlideController::class, 'show']);
        Route::delete('/{id}', [SlideController::class, 'destroy']);
        Route::put('/{id}', [SlideController::class, 'update']);
    });


    
