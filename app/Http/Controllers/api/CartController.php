<?php

namespace App\Http\Controllers\api;

use App\Helpers\AuthHelpers;
use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\User;
use http\Env\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class CartController extends Controller
{
    public function index(Request $request)
    {
        try {

            $token = $request->bearerToken();

            $user = AuthHelpers::CheckAuth($token);

            if ($user && $user->id) {
                $items = Cart::where('user_id', Auth::id())
                    ->with(
                        [
                            'productItem' => function ($query){
                                $query
                                    ->join('products', 'product_items.product_id', '=', 'products.id')
                                    ->select('products.name as product_name', 'product_items.*');

                                $query->with(['variants' => function ($query){
                                    $query
                                        ->join('variants', 'variant_options.variant_id', '=', 'variants.id')
                                        ->select('variants.name as variant_name', 'variant_options.*');
                                }]);
                            }
                        ]
                    )->get();
            } else {
                $items = collect(session('cart', []));
            }

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

    public function store(Request $request)
    {
        try {
            $request->validate(
                [
                    'product_item_id' => 'required|exists:product_items,id',
                    'quantity' => 'required|integer|min:1',
                ],
                [
                    'product_item_id.required' => 'Product item is required',
                    'product_item_id.exists' => 'Product item does not exist',
                    'quantity.required' => 'Quantity is required',
                    'quantity.integer' => 'Quantity must be an integer',
                    'quantity.min' => 'Quantity must be at least 1',
                ]
            );

            $productItemId = $request->input('product_item_id');
            $quantity = $request->input('quantity');

            $token = $request->bearerToken();

            $user = AuthHelpers::CheckAuth($token);

            if($user && $user->id){
                $cart = Cart::where('user_id', $user->id)->where('product_item_id', $productItemId)->first();

                if($cart){

                    $cart->quantity += $quantity;
                    $cart->save();

                }else{

                    Cart::create([
                        'user_id' => $user->id,
                        'product_item_id' => $productItemId,
                        'quantity' => $quantity
                    ]);

                }

                return response()->json([
                    'success' => true,
                    'message' => 'Thêm vào giỏ hàng thành công'
                ]);
            }

            $cart = collect(session('cart', []));
            $product = $cart->firstWhere('product_item_id', $productItemId);

            if($product) {

                $product['quantity'] += $quantity;

            } else {

                $cart->push([
                    'product_item_id' => $productItemId,
                    'quantity' => $quantity,
                ]);

            }

            session(['cart' => $cart->toArray()]);

            return response()->json([
                'success' => true,
                'message' => 'Thêm vào giỏ hàng thành công'
            ]);

        }catch (ValidationException $e){

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);

        }catch (\Exception $e){
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function updateQuantity(Request $request, $id)
    {
        try {
            $request->validate(
                [
                    'quantity' => 'required|integer|min:1',
                ],
                [
                    'quantity.required' => 'Quantity is required',
                    'quantity.integer' => 'Quantity must be an integer',
                    'quantity.min' => 'Quantity must be at least 1',
                ]
            );

            $quantity = $request->input('quantity');

            $token = $request->bearerToken();

            $user = AuthHelpers::CheckAuth($token);

            if($user && $user->id){

                $cart = Cart::where('user_id', $user->id)->where('product_item_id', $id)->first();
                $cart->quantity = $quantity;
                $cart->save();

            }else{

                $cart = collect(session('cart', []));
                $product = $cart->firstWhere('product_item_id', $id);

                if($product) {
                    $product['quantity'] = $quantity;
                }

            }

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật cart thành cônng'
            ]);

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

    public function destroy(Request $request, $id)
    {
        try {

            $token = $request->bearerToken();

            $user = AuthHelpers::CheckAuth($token);

            if($user && $user->id){

                Cart::where('user_id', $user->id)->where('product_item_id', $id)->delete();

            }else{

                $cart = collect(session('cart', []));

                $cart = $cart->reject(function ($item) use ($id) {
                    $item['product_item_id'] = $id;
                });

                session(['cart' => $cart->toArray()]);
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
}
