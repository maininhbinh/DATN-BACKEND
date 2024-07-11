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
            if (Auth::check()) {
                $items = Cart::where('user_id', Auth::id())->with(['productItem.variantOptions.variant'])->get();
            } else {
                $items = collect(session('cart', []));
            }

            $items = $items->map(function ($item) {
                return [
                    'id' => $item['id'],
                    'user_id' => $item['user_id'] ?? null,
                    'product_item_id' => $item['product_item_id'],
                    'quantity' => $item['quantity'],
                    'product' => [
                        'name' => $item['product']['name'],
                        'image' => $item['product']['image'],
                        'price' => $item['product']['price'],
                        'variant_options' => collect($item['product']['variant_options'])->map(function ($variantOption) {
                            return [
                                'id' => $variantOption['id'],
                                'name' => $variantOption['name'],
                                'variant' => [
                                    'id' => $variantOption['variant']['id'],
                                    'name' => $variantOption['variant']['name'],
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
            if (Auth::check()) {
                $cart = Cart::where('id', $id)->where('user_id', Auth::id())->firstOrFail();
                $cart->quantity = $request->quantity;
                $cart->save();
            } else {
                $cart = collect(session('cart', []));
                $itemKey = $cart->search(function ($item) use ($id) {
                    return $item['id'] == $id;
                });

                if ($itemKey !== false) {
                    $cart[$itemKey]['quantity'] = $request->quantity;
                    session(['cart' => $cart]);
                } else {
                    throw new \Exception('Item not found in cart');
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Cart updated successfully',
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
                'message' => 'Error updating cart.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            if (Auth::check()) {
                $cart = Cart::where('id', $id)->where('user_id', Auth::id())->firstOrFail();
                $cart->delete();
            } else {
                $cart = collect(session('cart', []));
                $itemKey = $cart->search(function ($item) use ($id) {
                    return $item['id'] == $id;
                });

                if ($itemKey !== false) {
                    $cart->forget($itemKey);
                    session(['cart' => $cart]);
                } else {
                    throw new \Exception('Item not found in cart');
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Item removed from cart'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Unable to remove item from cart',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroyAll()
    {
        try {
            if (Auth::check()) {
                $userId = Auth::id();
                Cart::where('user_id', $userId)->delete();
            } else {
                session()->forget('cart');
            }

            return response()->json([
                'success' => true,
                'message' => 'All items removed from cart'
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
