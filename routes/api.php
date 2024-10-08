<?php

use App\Http\Controllers\api\AttributeController;
use App\Http\Controllers\api\BrandController;
use App\Http\Controllers\api\CategoryController;
use App\Http\Controllers\api\AuthController;
use App\Http\Controllers\api\CartController;
use App\Http\Controllers\api\CommentController;
use App\Http\Controllers\api\CouponController;
use App\Http\Controllers\api\DetailController;
use App\Http\Controllers\api\OrderController;
use App\Http\Controllers\api\MomoController;
use App\Http\Controllers\api\RoleController;
use App\Http\Controllers\api\StatisticalController;
use App\Http\Controllers\api\UserController;
use App\Http\Controllers\api\ValueController;
use App\Http\Controllers\api\SlideController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\api\ProductController;
use App\Http\Controllers\api\StripeController;
use App\Http\Controllers\api\VariantController;
use App\Http\Controllers\api\VariantOptionController;
use App\Http\Controllers\api\VnPayController;
use App\Http\Controllers\api\PaymentController;

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
    Route::post('resend_token', [AuthController::class, 'resendToken']);
});

Route::prefix('user')->group(function () {
    Route::get('list', [UserController::class, 'index']);
    Route::get('profile', [UserController::class, 'profile'])->middleware('auth:sanctum');
    Route::post('', [UserController::class, 'store']);
    Route::get('{id}', [UserController::class, 'edit']);
    Route::post('/{id}', [UserController::class, 'update']);
    Route::delete('/{id}', [UserController::class, 'delete']);
    Route::put('/{id}/password', [UserController::class, 'updatePassword']);
});

Route::middleware('auth:sanctum')->prefix('cart')->group(function () {
    Route::get('', [CartController::class, 'index']);
    Route::post('add', [CartController::class, 'store']);
    Route::put('/{id}', [CartController::class, 'updateQuantity']);
    Route::delete('/{id}', [CartController::class, 'destroy']);
    Route::get('checkout', [CartController::class, 'checkout']);
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
    Route::get('page/{slug}', [CategoryController::class, 'page']);
    Route::post('', [CategoryController::class, 'store']);
    Route::get('list', [CategoryController::class, 'listCategory']);
    Route::get('{id}', [CategoryController::class, 'edit']);
    Route::get('show/{id}', [CategoryController::class, 'show']);
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
    Route::post('detail/name', [AttributeController::class, 'getByDetail']);
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
    Route::get('edit/{id}', [ProductController::class, 'edit']);
    Route::post('update/{id}', [ProductController::class, 'update']);
    Route::get('/{slug}', [ProductController::class, 'show']);
    Route::get('/home/{feat}', [ProductController::class, 'featProducts']);
    Route::get('{slug}/category', [ProductController::class, 'category']);
    Route::get('similar/{id}', [ProductController::class, 'getSimilarProducts']);
});

Route::prefix('slider')->group(function () {
    Route::post('/', [SlideController::class, 'store']);
    Route::get('/', [SlideController::class, 'show']);
    Route::delete('/{id}', [SlideController::class, 'destroy']);
    Route::get('/{id}', [SlideController::class, 'edit']);
    Route::post('/{id}', [SlideController::class, 'update']);
});

Route::prefix('order')->middleware('auth:sanctum')->group(function () {
    Route::get('user', [OrderController::class, 'getAllOrder']);
    Route::get('today', [OrderController::class, 'getOrderToday']);
    Route::post('cancel', [OrderController::class, 'orderCancel']);
    Route::get('detail/{id}', [OrderController::class, 'getOrderDetail']);
    Route::get('', [OrderController::class, 'index']);
    Route::post('', [OrderController::class, 'placeOrder']);
    Route::get('{id}', [OrderController::class, 'show']);
    Route::put('update/status/{id}', [OrderController::class, 'updateStatus'])->middleware('check.status');
    Route::post('process-payment/{order_id}', [OrderController::class, 'processPayment']); //đã lấy vào id và qua 1 hàm mới để chọn method thanh toán
});

Route::prefix('variant')->group(function () {
    Route::get('', [VariantController::class, 'index']);
    Route::post('', [VariantController::class, 'store']);
    Route::get('/{id}', [VariantController::class, 'edit']);
    Route::post('/{id}', [VariantController::class, 'update']);
    Route::delete('/{id}', [VariantController::class, 'destroy']);
    Route::post('/{id}/restore', [VariantController::class, 'restore']);
});

Route::post('option', [VariantController::class, 'show']);

Route::prefix('variant_option')->group(function () {
    Route::get('', [VariantOptionController::class, 'index']);
    Route::post('', [VariantOptionController::class, 'store']);
    Route::get('/{id}', [VariantOptionController::class, 'edit']);
    Route::post('/{id}', [VariantOptionController::class, 'update']);
    Route::delete('/{id}', [VariantOptionController::class, 'destroy']);
    Route::post('/{id}/restore', [VariantOptionController::class, 'restore']);
});

Route::prefix('payment')->group(function () {
    Route::get('momo/{orderId}', [MomoController::class, 'momoPayment']);
    Route::get('momo_fallback', [MomoController::class, 'fallBack']);
    Route::post('stripe/{order_id}', [StripeController::class, 'stripePayment']);
    Route::get('vnpay/{orderId}', [VnPayController::class, 'vnpayPayment']);
    Route::get('vnpay_callback', [VnPayController::class, 'returnCallBack']);
});
Route::get('payment/{orderId}', [PaymentController::class, 'payment']);


Route::prefix('coupon')->group(function () {
    Route::post('apply', [CouponController::class, 'apply']);
    Route::get('', [CouponController::class, 'index']);
    Route::post('', [CouponController::class, 'store']);
    Route::get('/{id}', [CouponController::class, 'edit']);
    Route::post('/{id}', [CouponController::class, 'update']);
    Route::delete('/{id}', [CouponController::class, 'destroy']);
    Route::post('apply', [CouponController::class, 'apply'])->middleware('auth:sanctum');
});

Route::prefix('filter')->group(function () {
    Route::get('/search', [ProductController::class, 'search']);
});

Route::middleware('auth:sanctum')->prefix('comment')->group(function () {
    Route::get('/products/{productId}/comments', [CommentController::class, 'index']);
    Route::post('', [CommentController::class, 'store']);
    Route::delete('/{id}', [CommentController::class, 'destroy']);
});

Route::prefix('statistical')->group(function () {
    Route::get('today', [StatisticalController::class, 'today']);
});


