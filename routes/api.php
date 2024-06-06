<?php

use App\Http\Controllers\api\ApiAttribitesController;
use App\Http\Controllers\api\ApiCategoriesController;
use App\Http\Controllers\api\ApiParamtersController;
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


Route::prefix('category')->group(function () {
    // lớp cha
    Route::get('/', [ApiCategoriesController::class, 'index']);
    Route::post('/', [ApiCategoriesController::class, 'store']);
    //update lớp cha (category)
    Route::post('/{id}', [ApiCategoriesController::class, 'update']);
    // lớp con
    Route::get('/{id}', [ApiCategoriesController::class, 'show']); // show lớp con
    Route::post('/child', [ApiCategoriesController::class, 'storeChild']); //thêm lớp con
    Route::post('/{id}/children/{child_id}', [ApiCategoriesController::class, 'updateChild']);
    //xoá lớp cha(đã kiểm tra sự tồn tại nếu có lớp con)
    Route::delete('/{id}/deleteCategory', [ApiCategoriesController::class, 'deleteCategory']);
});

// parameter
Route::prefix('parameter')->group(function () {
    Route::get('/', [ApiParamtersController::class, 'index'])->name('parameter.index');
    Route::post('/', [ApiParamtersController::class, 'store'])->name('parameter.store');
    Route::get('/{id}', [ApiParamtersController::class, 'show'])->name('parameter.show');
    Route::put('/{id}', [ApiParamtersController::class, 'update'])->name('parameter.update');
    Route::delete('/{id}', [ApiParamtersController::class, 'destroy'])->name('parameter.destroy');
});
