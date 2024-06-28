<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    public function index()
    {
        try {

            $items = Cart::where('user_id', Auth::id())->get();
            return response()->json([
                'success' => true,
                'data' => $items
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể truy xuất các mục trong giỏ hàng.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function store(Request $request)
    {
        try {
            $request->validate([
                'product_attr_id' => 'required|exists:product_attrs,id',
                'quantity' => 'required|integer|min:1',
            ]);

            $cart = Cart::updateOrCreate(
                [
                    'user_id' => Auth::id(),
                    'product_attr_id' => $request->product_attr_id,
                ],
                [
                    'quantity' => $request->quantity,
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Sản phẩm đã được thêm vào giỏ hàng',
                'data' => $cart
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi không thể thêm vào giỏ hàng',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $request->validate([
                'quantity' => 'required|integer|min:1',
            ]);

            $cart = Cart::where('id', $id)->where('user_id', Auth::id())->firstOrFail();
            $cart->quantity = $request->quantity;
            $cart->save();

            return response()->json([
                'success' => true,
                'message' => 'Giỏ hàng đã được update',
                'data' => $cart
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi update giỏ hàng.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $cart = Cart::where('id', $id)->where('user_id', Auth::id())->firstOrFail();
            $cart->delete();

            return response()->json([
                'success' => true,
                'message' => 'Sản phẩm đã xoá khỏi giỏ hàng'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi không xoá khỏi giỏ hàng được',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
