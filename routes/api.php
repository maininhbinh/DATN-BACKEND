<?php

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
