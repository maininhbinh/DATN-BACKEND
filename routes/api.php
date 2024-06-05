<?php

use App\Http\Controllers\api\ApiAttribitesController;
use App\Http\Controllers\api\ApiCategoriesController;
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

Route::middleware('api')
    ->prefix('auth')
    ->group(function () {

        Route::post('signup', [AuthController::class, 'signup']);
        Route::post('verifyOTP', [AuthController::class, 'verifyOTP']);
        Route::post('login', [AuthController::class, 'login']);
        Route::post('refreshToken', [AuthController::class, 'refreshToken'])->middleware(['jwt.verify']);
        Route::post('logout', [AuthController::class, 'logout'])->middleware(['jwt.verify']);
    });

Route::middleware(['api', 'jwt.verify'])
    ->prefix('user')
    ->group(function () {

        Route::get('profile', [UserController::class, 'profile']);
    });
// lớp cha
Route::get('categories', [ApiCategoriesController::class, 'index']);
Route::post('categories', [ApiCategoriesController::class, 'store']);
//update lớp cha (category)
Route::post('/categories/{id}', [ApiCategoriesController::class, 'update']);
//xoá lớp cha(đã kiểm tra sự tồn tại nếu có lớp con)
Route::delete('/categories/{id}/deleteCategory', [ApiCategoriesController::class, 'deleteCategory']);

// lớp con
Route::get('/categories/{id}', [ApiCategoriesController::class, 'show']);
Route::post('/categories/child', [ApiCategoriesController::class, 'storeChild']);
Route::post('/categories/{id}/children/{child_id}', [ApiCategoriesController::class, 'updateChild']);


//
Route::prefix('attributes')->group(function () {
    Route::get('/', [ApiAttribitesController::class, 'index'])->name('attributes.index');
    Route::post('/', [ApiAttribitesController::class, 'store'])->name('attributes.store');
    Route::get('/{id}', [ApiAttribitesController::class, 'show'])->name('attributes.show');
    Route::put('/{id}', [ApiAttribitesController::class, 'update'])->name('attributes.update');
    Route::delete('/{id}', [ApiAttribitesController::class, 'destroy'])->name('attributes.destroy');
});