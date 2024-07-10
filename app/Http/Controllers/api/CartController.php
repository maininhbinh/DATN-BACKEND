<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class CartController extends Controller
{
    public function index()
    {
        try {
            $items = Cart::where('user_id', Auth::id())->with(['productItem.variantOptions.variant'])->get();
    
            $items = $items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'user_id' => $item->user_id,
                    'product_item_id' => $item->product_item_id,
                    'quantity' => $item->quantity,
                    'product' => [
                        'name' => $item->productItem->name,
                        'image' => $item->productItem->image,
                        'price' => $item->productItem->price,
                        'variant_options' => $item->productItem->variantOptions->map(function ($variantOption) {
                            return [
                                'id' => $variantOption->id,
                                'name' => $variantOption->name,
                                'variant' => [
                                    'id' => $variantOption->variant->id,
                                    'name' => $variantOption->variant->name,
                                ],
                            ];
                        }),
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
                'message' => 'Cannot retrieve cart items.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $this->validateRequest($request);

        try {
            $cart = Cart::where('id', $id)->where('user_id', Auth::id())->firstOrFail();
            $cart->quantity = $request->quantity;
            $cart->save();

            return response()->json([
                'success' => true,
                'message' => 'Giỏ hàng đã được update',
                'data' => $cart
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
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
                'message' => 'Không xoá khỏi giỏ hàng được',
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
                'message' => 'Unable to clear cart',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    private function validateRequest(Request $request)
    {
        $request->validate([
            'product_item_id' => 'required|exists:products_item,id',
            'quantity' => 'required|integer|min:1',
        ], [
            'product_item_id.required' => 'Product item is required',
            'product_item_id.exists' => 'Product item does not exist',
            'quantity.required' => 'Quantity is required',
            'quantity.integer' => 'Quantity must be an integer',
            'quantity.min' => 'Quantity must be at least 1',
        ]);
    }
}
