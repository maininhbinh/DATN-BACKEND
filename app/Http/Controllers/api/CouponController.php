<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\UserCoupon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class CouponController extends Controller
{
    public function index()
    {
        try {
            $coupons = Coupon::orderBy('created_at', 'desc')->get();
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
            'value' => 'nullable|integer|min:0',
            'type' => 'required|in:number,percent,free_ship',
            'start_date' => 'required|date|after:now',
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
            'discount_max' => 'sometimes|required|integer|min:0',
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

        try {
            $coupon = Coupon::where('code', $request->code)
                ->where('end_date', '>', Carbon::now())
                ->where('is_activate', 1)
                ->first();

            if (!$coupon) {
                return response()->json([
                    'success' => false,
                    'message' => 'Coupon không hợp lệ hoặc đã hết hạn.'
                ], 200);
            }

            if ($coupon->used_count >= $coupon->quantity) {
                return response()->json([
                    'success' => false,
                    'message' => 'Coupon đã hết số lượng sử dụng.'
                ], 200);
            }

            return response()->json([
                'success' => true,
                'name' => $coupon->name,
                'value' => $coupon->value,
                'type' => $coupon->type,
                'discount_max' => $coupon->discount_max
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
