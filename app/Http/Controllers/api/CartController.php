<?php

namespace App\Http\Controllers\api;

use App\Helpers\AuthHelpers;
use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\ProductItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class CartController extends Controller
{
    public function index(Request $request)
    {
        try {

            $items = Cart::where('user_id', Auth::id())
                ->join('product_items', 'carts.product_item_id', '=', 'product_items.id')
                ->join('products', 'product_items.product_id', '=', 'products.id')
                ->select('carts.id','products.name', 'products.thumbnail', 'products.slug', 'carts.quantity', 'carts.user_id', 'carts.product_item_id', 'product_items.quantity as quantity_product')
                ->with('productItem.variants')
                ->get();

            $items = $items->map(function($item){
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'slug' => $item->slug,
                    'thumbnail' => $item->thumbnail,
                    'quantity' => $item->quantity,
                    'user_id' => $item->user_id,
                    'product_item_id' => $item->product_item_id,
                    'price' => $item->productItem->price,
                    'price_sale' => $item->productItem->price_sale,
                    'image' => $item->productItem->image,
                    'variants' => $item->productItem->variants,
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

    public function store(Request $request)
    {
        try {
            $request->validate(
                [
                    'quantity' => 'required|integer|min:1',
                    'product_item_id' => 'required|exists:product_items,id',
                ],
                [
                    'quantity.required' => 'Bắt buộc phải có quantity',
                    'quantity.integer' => 'Quantity phải là kiểu số',
                    'quantity.min' => 'Quantity phải nhỏ hơn hoặc bằng 1',
                    'product_item_id.required' => 'Product item is required',
                    'product_item_id.exists' => 'Product item does not exist',
                ]
            );

            $productItemId = $request->input('product_item_id');
            $quantity = $request->input('quantity');

            $user = $request->user();

            if($user && $user->id){
                $cart = Cart::where('user_id', $user->id)
                    ->where('product_item_id', $productItemId)
                    ->join('product_items', 'carts.product_item_id', '=', 'product_items.id')
                    ->select('carts.*', 'product_items.quantity as quantity_product')
                    ->first();

                if($cart && $cart->id){

                    Log::channel('debug')->debug($quantity);

                    $cart->quantity += $quantity;

                    if($cart->quantity > $cart->quantity_product){
                        return response()->json([
                            'success' => false,
                            'message' => 'Số lương đơn hàng vượt quá số lượng sản phâm'
                        ], 422);
                    }

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
                    'message' => 'Update success'
                ], 200);
            }

        }catch (ValidationException $e){

            return response()->json([
                'success' => false,
                'message' => $e->getLine(),
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

            $user = $request->user();

            if($user && $user->id){

                $cart = Cart::where('user_id', $user->id)->where('product_item_id', $id)->first();
                $cart->quantity = $quantity;

                if($cart->quantity < $cart->quantity_product){
                    return response()->json([
                        'success' => false,
                        'message' => 'Số lương đơn hàng vượt quá số lượng sản phâm'
                    ], 422);
                }

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
                'errors' => $e->getLine()
            ], 422);

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => 'Error updating cart.',
                'error' => $e->getLine()
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
