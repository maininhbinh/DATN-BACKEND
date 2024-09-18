<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\UserCoupon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class CouponController extends Controller
{
    public function index()
    {
        try {

            $coupons = Coupon::where('end_date', '>', Carbon::now())
                ->orderBy('id', 'desc')
                ->get();
            return response()->json(['success' => true, 'data' => $coupons], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi khi lấy dữ liệu.'
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:255|unique:coupons,code',
            'quantity' => 'required|integer|min:1',
            'used_count' => 'required|integer|min:1',
            'value' => 'nullable|integer|min:0',
            'type' => 'required|in:number,percent,free_ship',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after:start_date',
            'discount_max' => 'required|integer|min:0',
            'is_activate' => 'required|integer|in:0,1',
            'status' => 'required|in:public,private',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()
            ], 400);
        }
        try {
            $item = Coupon::create($request->all());
            return response()->json([
                'success' => true,
                'data' => $item
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e
            ], 500);
        }
    }


    public function edit(string $id)
    {
        try {
            $item = Coupon::findOrFail($id);
            return response()->json(['success' => true, 'data' => $item], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy Coupon.'
            ], 404);
        }
    }

    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'code' => 'sometimes|required|string|max:255|unique:coupons,code,' . $id,
            'quantity' => 'sometimes|required|integer|min:1',
            'value' => 'nullable|integer|min:0',
            'type' => 'sometimes|required|in:number,percent,free_ship',
            'start_date' => 'sometimes|required|date|after:now',
            'end_date' => 'sometimes|required|date|after:start_date',
            'is_activate' => 'sometimes|required|integer|in:0,1',
            'status' => 'sometimes|required|in:public,private',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()
            ], 400);
        }

        try {
            $item = Coupon::findOrFail($id);
            $item->update($request->all());
            return response()->json([
                'success' => true,
                'data' => $item
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi khi cập nhật dữ liệu.'
            ], 500);
        }
    }

    public function destroy(string $id)
    {
        try {
            $item = Coupon::findOrFail($id);
            $item->delete();
            return response()->json(['success' => true, 'message' => 'Coupon deleted successfully'], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while deleting the coupon.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function apply(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()
            ], 400);
        }

        $user = $request->user();

        try {
            $carts = Cart::where('user_id', $user->id)
                ->join('product_items', 'carts.product_item_id', '=', 'product_items.id')
                ->join('products', 'product_items.product_id', '=', 'products.id')
                ->select(
                    'carts.*',
                    DB::raw('IFNULL(price_sale, price) as price')
                )
                ->get();

            if (count($carts) <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Giỏ hàng ít nhất phải có 1 sản phẩm'
                ], 404);
            }

            $totalPrice = 0;

            foreach ($carts as $cart) {
                $totalPrice += ($cart->price_sale ?? $cart->price) * $cart->quantity;
            }

            $coupon = Coupon::where('code', $request->code)
                ->where('end_date', '>', Carbon::now())
                ->where('is_activate', 1)
                ->first();

            $use = $user->coupons()->where('code', $request->code)->get();

            if(count($use) >= $coupon->used_count){
                return response()->json([
                    'success' => false,
                    'message' => 'Khách hàng đã hết lượt sử dụng'
                ], 422);
            }

            if($coupon->discount_max > $totalPrice){
                return response()->json([
                    'success' => false,
                    'message' => 'Đơn hàng tối thiểu là '> $coupon->discount_max
                ], 422);
            }

            if (!$coupon) {
                return response()->json([
                    'success' => false,
                    'message' => 'Coupon không hợp lệ hoặc đã hết hạn.'
                ], 422);
            }

            if ($coupon->quantity < 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'Coupon đã hết số lượng sử dụng.'
                ], 422);
            }

            $discount = $coupon->value;

            if($coupon->type == 'percent'){
                $discount = ($coupon->value / 100) * $totalPrice;
            }

            $use = $user->with(['coupons' => function ($query) use ($request) {
                $query->where('code', $request->code);
            }]);

            return response()->json([
                'success' => true,
                'code' => $coupon->code,
                'discount' => $discount,
                'user' => $user
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi khi áp dụng mã giảm giá.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
