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
            $items = Cart::where('user_id', Auth::id())->with('productItem')->get();

            $items = $items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'user_id' => $item->user_id,
                    'product_item_id' => $item->product_item_id,
                    'quantity' => $item->quantity,
                    'product' => [
                        'image' => $item->productItem->image,
                        'price' => $item->productItem->price,
                    ],
                ];
            });

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
                'product_item_id' => 'required|exists:products_item,id',
                'quantity' => 'required|integer|min:1',
            ]);

            $cart = Cart::updateOrCreate(
                [
                    'user_id' => Auth::id(),
                    'product_item_id' => $request->product_item_id,
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

    public function destroyAll()
    {
        try {
            $userId = Auth::id();
            Cart::where('user_id', $userId)->delete();
            return response()->json([
                'success' => true,
                'message' => 'Tất cả sản phẩm đã xoá khỏi giỏ hàng'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi không xoá giỏ hàng được',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
