<?php

namespace App\Http\Controllers\api;

use App\Models\OrderStatus;
use App\Enums\OrderStatus as EnumOrderStatus;
use App\Enums\PaymentStatuses;
use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\OrderHistory;
use App\Models\PaymentMethod;
use App\Models\ProductItem;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{

    public function getOrderToday(Request $request){
        try {
            $item = Order::whereDate('orders.created_at', Carbon::today())
                ->join('order_statuses', 'orders.order_status_id', '=', 'order_statuses.id')
                ->join('payment_statuses', 'orders.payment_status_id', '=', 'payment_statuses.id')
                ->select('orders.*', 'order_statuses.name as order_status_name', 'payment_statuses.name as payment_status_name')
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'sucess' => true,
                'data' => $item
            ], 200);
        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => $e
            ], 500);
        }
    }

    public function index()
    {
        try {

            $item = Order::orderBy('created_at', 'desc')
                ->join('order_statuses', 'orders.order_status_id', '=', 'order_statuses.id')
                ->select('orders.*', 'order_statuses.name as status_name') // Chỉ chọn các cột cần thiết
                ->get();

            return response()->json([
                'sucess' => true,
                'data' => $item
            ], 200);
        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => $e
            ], 500);
        }
    }

    /**í
     * Display a listing of the resource.
     */
    public function getAllOrder(Request $request)
    {
        try {
            $user = $request->user();

            $orderDetail = Order::where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->with(['orderDetails' => function ($query) {
                    $query
                        ->with(['productItem' => function ($query) {
                            $query
                                ->with(['variants' => function ($query) {
                                    $query->orderBy('product_configurations.id', 'asc')
                                        ->join('variants', 'variant_options.variant_id', '=', 'variants.id')
                                        ->select('variant_options.*', 'variants.name as variant_name')
                                        ->get();
                                }])
                                ->join('products', 'product_items.product_id', '=', 'products.id')
                                ->select('product_items.*', 'products.name', 'products.thumbnail');
                        }]);
                }])
                ->join('payment_statuses', 'orders.payment_status_id', '=', 'payment_statuses.id')
                ->join('payment_methods', 'orders.payment_method_id', '=', 'payment_methods.id')
                ->join('order_statuses', 'orders.order_status_id', '=', 'order_statuses.id')
                ->select(
                    'orders.id',
                    'orders.user_id',
                    'orders.total_price',
                    'orders.discount_price',
                    'orders.discount_code',
                    'orders.discount_code',
                    'orders.note',
                    'orders.sku as code',
                    'orders.created_at',
                    'payment_statuses.name as payment_status',
                    'order_status_id',
                    'order_statuses.name as order_status',
                    'payment_methods.code as payment_methods',
                )
                ->get();

            $order = $orderDetail->map(function ($item) {
                return [
                    'id' => $item->id,
                    'total_price' => $item->total_price,
                    'discount_price' => $item->discount_price,
                    'note' => $item->note,
                    'code' => $item->code,
                    'created_at' => $item->created_at,
                    'order_status' => $item->order_status,
                    'payment_status' => $item->payment_status,
                    'payment_methods' => $item->payment_methods,
                    'order_details' => $item->orderDetails->map(function ($item) {
                        return [
                            'id' => $item->id,
                            'quantity' => $item->quantity,
                            'price' => $item->price,
                            'name' => $item->productItem->name,
                            'sku' => $item->productItem->sku,
                            'image' => $item->productItem->image,
                            'thumbnail' => $item->productItem->thumbnail,
                            'varians' => $item->productItem->variants
                        ];
                    })->toArray(),
                ];
            });

            return response()->json([
                'sucess' => true,
                'data' => $order
            ], 200);
        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
            ], 500);
        }
    }

    public function getOrderDetail(Request $request, $id)
    {
        try {
            $request->user();

            $orderDetail = Order::where('orders.id', $id)
                ->where('orders.user_id', $request->user()->id)
                ->with(['orderDetails' => function ($query) {
                    $query
                        ->with(['productItem' => function ($query) {
                            $query
                                ->with(['variants' => function ($query) {
                                    $query->orderBy('product_configurations.id', 'asc')
                                        ->join('variants', 'variant_options.variant_id', '=', 'variants.id')
                                        ->select('variant_options.*', 'variants.name as variant_name')
                                        ->get();
                                }])
                                ->join('products', 'product_items.product_id', '=', 'products.id')
                                ->select('product_items.*', 'products.name', 'products.thumbnail');
                        }]);
                }])
                ->with(['histories' => function ($query) {
                    $query
                        ->join('order_statuses', 'order_histories.order_status_id', '=', 'order_statuses.id')
                        ->select('order_histories.*', 'order_statuses.name');
                }])
                ->with(['user'])
                ->join('payment_statuses', 'orders.payment_status_id', '=', 'payment_statuses.id')
                ->join('payment_methods', 'orders.payment_method_id', '=', 'payment_methods.id')
                ->join('order_statuses', 'orders.order_status_id', '=', 'order_statuses.id')
                ->select(
                    'orders.id',
                    'orders.user_id',
                    'orders.total_price',
                    'orders.receiver_name',
                    'orders.receiver_phone',
                    'orders.receiver_email',
                    'orders.receiver_city',
                    'orders.receiver_district',
                    'orders.receiver_ward',
                    'orders.receiver_address',
                    'orders.discount_price',
                    'orders.discount_code',
                    'orders.discount_code',
                    'orders.note',
                    'orders.sku as code',
                    'orders.created_at',
                    'payment_statuses.name as payment_status',
                    'order_status_id',
                    'order_statuses.name as order_status',
                    'payment_methods.code as payment_methods',
                )
                ->first();

            $orderStatuses = OrderStatus::all();

            if (!$orderDetail) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found'
                ], 404);
            }

            $order = [
                'id' => $orderDetail->id,
                'total_price' => $orderDetail->total_price,
                'receiver_name' => $orderDetail->receiver_name,
                'receiver_phone' => $orderDetail->receiver_phone,
                'receiver_email' => $orderDetail->receiver_email,
                'receiver_city' => $orderDetail->receiver_city,
                'receiver_district' => $orderDetail->receiver_district,
                'receiver_ward' => $orderDetail->receiver_ward,
                'receiver_address' => $orderDetail->receiver_address,
                'discount_price' => $orderDetail->discount_price,
                'note' => $orderDetail->note,
                'code' => $orderDetail->code,
                'created_at' => $orderDetail->created_at,
                'order_status' => [
                    'id' => $orderDetail->order_status_id,
                    'status' => $orderDetail->order_status
                ],
                'payment_status' => $orderDetail->payment_status,
                'payment_methods' => $orderDetail->payment_methods,
                'order_details' => $orderDetail->orderDetails->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'quantity' => $item->quantity,
                        'price' => $item->price,
                        'name' => $item->productItem->name,
                        'sku' => $item->productItem->sku,
                        'image' => $item->productItem->image,
                        'thumbnail' => $item->productItem->thumbnail,
                        'varians' => $item->productItem->variants
                    ];
                })->toArray(),
                'histories' => $orderDetail->histories->map(function ($history) {
                    return [
                        'id' => $history->id,
                        'status_name' => $history->name,
                        'status_id' => $history->order_status_id,
                        'created_at' => $history->created_at,
                        'updated_at' => $history->updated_at
                    ];
                })->toArray(),
                'user' => $orderDetail->user,
            ];

            return response()->json([
                'success' => true,
                'order_detail' => $order,
                'order_status' => $orderStatuses
            ]);
        } catch (\Exception $exception) {
            return response()->json([
                'success' => false,
                'massage' => $exception->getMessage()
            ]);
        }
    }

    public function show($id)
    {
        try {
            $orderDetail = Order::where('orders.id', $id)
                ->with(['orderDetails' => function ($query) {
                    $query
                        ->with(['productItem' => function ($query) {
                            $query
                                ->with(['variants' => function ($query) {
                                    $query->orderBy('product_configurations.id', 'asc')
                                        ->join('variants', 'variant_options.variant_id', '=', 'variants.id')
                                        ->select('variant_options.*', 'variants.name as variant_name')
                                        ->get();
                                }])
                                ->join('products', 'product_items.product_id', '=', 'products.id')
                                ->select('product_items.*', 'products.name', 'products.thumbnail');
                        }]);
                }])
                ->with(['histories' => function ($query) {
                    $query
                        ->join('order_statuses', 'order_histories.order_status_id', '=', 'order_statuses.id')
                        ->select('order_histories.*', 'order_statuses.name');
                }])
                ->with(['user'])
                ->join('payment_statuses', 'orders.payment_status_id', '=', 'payment_statuses.id')
                ->join('payment_methods', 'orders.payment_method_id', '=', 'payment_methods.id')
                ->join('order_statuses', 'orders.order_status_id', '=', 'order_statuses.id')
                ->select(
                    'orders.id',
                    'orders.user_id',
                    'orders.total_price',
                    'orders.receiver_name',
                    'orders.receiver_phone',
                    'orders.receiver_email',
                    'orders.receiver_city',
                    'orders.receiver_district',
                    'orders.receiver_ward',
                    'orders.receiver_address',
                    'orders.discount_price',
                    'orders.discount_code',
                    'orders.discount_code',
                    'orders.note',
                    'orders.sku as code',
                    'orders.created_at',
                    'payment_statuses.name as payment_status',
                    'order_status_id',
                    'order_statuses.name as order_status',
                    'payment_methods.code as payment_methods',
                )
                ->first();

            $orderStatuses = OrderStatus::all();

            if (!$orderDetail) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found'
                ], 404);
            }

            $order = [
                'id' => $orderDetail->id,
                'total_price' => $orderDetail->total_price,
                'receiver_name' => $orderDetail->receiver_name,
                'receiver_phone' => $orderDetail->receiver_phone,
                'receiver_email' => $orderDetail->receiver_email,
                'receiver_city' => $orderDetail->receiver_pronvinces,
                'receiver_district' => $orderDetail->receiver_district,
                'receiver_ward' => $orderDetail->receiver_ward,
                'receiver_address' => $orderDetail->receiver_address,
                'discount_price' => $orderDetail->discount_price,
                'note' => $orderDetail->note,
                'code' => $orderDetail->code,
                'created_at' => $orderDetail->created_at,
                'order_status' => [
                    'id' => $orderDetail->order_status_id,
                    'status' => $orderDetail->order_status
                ],
                'payment_status' => $orderDetail->payment_status,
                'payment_methods' => $orderDetail->payment_methods,
                'order_details' => $orderDetail->orderDetails->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'quantity' => $item->quantity,
                        'price' => $item->price,
                        'name' => $item->productItem->name,
                        'sku' => $item->productItem->sku,
                        'image' => $item->productItem->image,
                        'thumbnail' => $item->productItem->thumbnail,
                        'varians' => $item->productItem->variants
                    ];
                })->toArray(),
                'histories' => $orderDetail->histories->map(function ($history) {
                    return [
                        'id' => $history->id,
                        'status_name' => $history->name,
                        'status_id' => $history->order_status_id,
                        'created_at' => $history->created_at,
                        'updated_at' => $history->updated_at
                    ];
                })->toArray(),
                'user' => $orderDetail->user,
            ];

            return response()->json([
                'success' => true,
                'order_detail' => $order,
                'order_status' => $orderStatuses
            ]);
        } catch (\Exception $exception) {
            return response()->json([
                'success' => false,
                'massage' => $exception->getMessage()
            ]);
        }
    }

    public function placeOrder(Request $request)
    {
        try {
            DB::beginTransaction();

            $request->validate(
                [
                    'receiver_name' => 'required|string',
                    'receiver_phone' => ['required', 'regex:/^(\+84|84|0)(3|5|7|8|9)[0-9]{8}$/'],
                    'receiver_email' => 'required|string|email',
                    'receiver_city' => 'required|string',
                    'receiver_district' => 'required|string',
                    'receiver_ward' => 'required|string',
                    'receiver_address' => 'required|string',
                    'payment_method' => 'required|string'
                ],
                [
                    'receiver_phone.regex' => 'Số điện thoại phải đúng định dạng và bắt đầu bằng +84.',
                    'receiver_name.required' => 'Trường name là bắt buộc',
                    'receiver_name.string' => 'Trường name phải là một chuỗi',
                    'receiver_phone.required' => 'Trường phone là bắt buộc',
                    'receiver_phone.string' => 'Trường phone phải là một chuỗi',
                    'receiver_city.required' => 'Bắt buộc chọn thánh phố',
                    'receiver_district.required' => 'Chọn một quận huyện',
                    'receiver_ward.required' => 'Chọn một xã phường',
                    'receiver_address.required' => 'Trường địa chỉ là bắt buộc',
                    'payment_method.required' => 'Thiếu hình thức thanh toán'
                ]
            );
            $paymentMethod = $request->get('payment_method');

            $payment_method_model = PaymentMethod::where('code', $paymentMethod)->first();

            $receiverName = $request->get('receiver_name');
            $receiverPhone = $request->get('receiver_phone');
            $receiverEmail = $request->get('receiver_email');
            $receiverProvinces = $request->get('receiver_city');
            $receiverDistrict = $request->get('receiver_district');
            $receiverWard = $request->get('receiver_ward');
            $receiverAddress = $request->get('receiver_address');
            $note = $request->get('note');
            $discountCode = $request->get('discount_code');

            $paymentStatusId = PaymentStatuses::getOrder(PaymentStatuses::PENDING);
            $orderStatusId = EnumOrderStatus::getOrder(EnumOrderStatus::PENDING);

            $user = $request->user();

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

            // Xử lý discount code (nếu có logic xử lý)
            $discountPrice = 0; // Thay đổi nếu có logic xử lý discount code

            if($discountCode){
                $coupon = Coupon::where('code', $discountCode)
                    ->where('end_date', '>', Carbon::now())
                    ->where('is_activate', 1)
                    ->first();

                $use = $user->coupons()->where('code', $discountCode)->get();

                if(count($use) >= $coupon->used_count){
                    return response()->json([
                        'success' => false,
                        'message' => 'Mã giảm giá không hợp lệ'
                    ], 422);
                }

                if($coupon && $coupon->discount_max < $totalPrice && $coupon->quantity > 1){
                    $discountCode = $coupon->code;
                    $discountPrice = $coupon->value;

                    if($coupon->type == 'percent'){
                        $discountPrice = ($coupon->value / 100) * $totalPrice;
                    }

                    $coupon->update([
                        'quantity' => $coupon->quantity - 1
                    ]);

                    $user->coupons()->attach($coupon->id, ['action' => 'redeemed']);
                }

            }

            $totalPrice = $totalPrice - $discountPrice;

            $order = Order::create([
                'user_id' => $user->id,
                'total_price' => $totalPrice,
                'note' => $note,
                'order_status_id' => $orderStatusId,
                'receiver_name' => $receiverName,
                'receiver_phone' => $receiverPhone,
                'receiver_email' => $receiverEmail,
                'receiver_city' => $receiverProvinces,
                'receiver_district' => $receiverDistrict,
                'receiver_ward' => $receiverWard,
                'receiver_address' => $receiverAddress,
                'payment_method_id' => $payment_method_model->id,
                'payment_status_id' => $paymentStatusId,
                'discount_code' => $discountCode,
                'discount_price' => $discountPrice,
            ]);

            OrderHistory::query()->create([
                'order_id' => $order->id,
                'order_status_id' => $order->order_status_id,
            ]);

            foreach ($carts as $cart) {
                OrderDetail::create([
                    'product_item_id' => $cart->product_item_id,
                    'order_id' => $order->id,
                    'quantity' => $cart->quantity,
                    'price' => $cart->price,
                ]);

                $product_item = ProductItem::find($cart->product_item_id);

                if($product_item->quantity < 1){
                    return response()->json([
                        'success' => false,
                        'message' => 'số lượng sản phẩm đã hêt',
                    ], 422);
                }

                $product_item->update([
                    'quantity' => $product_item->quantity -1
                ]);
            }

            Cart::where('user_id', $user->id)->delete();

            DB::commit();

            return redirect()->action([PaymentController::class, 'payment'], ['orderId' => $order->id]);
        } catch (ValidationException $validationException) {
            return response()->json([
                'success' => false,
                'message' => $validationException->getMessage()
            ], 422);
        } catch (\Exception $exception) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage()
            ], 500);
        }
    }

    public function updateStatus(Request $request, $id)
    {
        try {

            $orderStatus = $request->input('status');

            $order = Order::findOrFail($id);
            $order->order_status_id = $orderStatus;
            $order->save();

            OrderHistory::query()->create([
                'order_id' => $order->id,
                'order_status_id' => $order->order_status_id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật trạng thái thành công'
            ]);
        } catch (\Exception $exception) {
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage()
            ]);
        }
    }

    public function processPayment(Request $request, $order_id)
    {
        $request->validate([
            'payment_method' => 'required|string|in:momo,stripe,vnpay',
        ]);

        $paymentMethod = $request->get('payment_method');

        switch ($paymentMethod) {
            case 'momo':
                return response()->json([
                    'success' => true,
                    'redirect_url' => url("/payment/momo/{$order_id}")
                ]);
            case 'stripe':
                return response()->json([
                    'success' => true,
                    'redirect_url' => url("/payment/stripe/{$order_id}")
                ]);
            case 'vnpay':
                return response()->json([
                    'success' => true,
                    'redirect_url' => url("/payment/vnpay/{$order_id}")
                ]);
            default:
                return response()->json([
                    'success' => false,
                    'message' => 'Hình thức thanh toán không hợp lệ.'
                ]);
        }
    }

    public function orderCancel(Request $request){

        try {
            $request->validate([
                'order_id' => 'required|integer|exists:orders,id',
                'note' => 'required|string',
            ]);

            $note = $request->get('note');

            $order = Order::query()->findOrFail($request->get('order_id'));

            if($order->order_status_id != \App\Enums\OrderStatus::getOrder(EnumOrderStatus::PENDING)){
                return response()->json([
                    'success' => false,
                    'message' => 'Đơn hàng không được hủy. Đơn hàng phảiở trạng thái "đang chờ sử lý".'
                ]);
            }

            $order->update([
                'order_status_id' => EnumOrderStatus::getOrder(EnumOrderStatus::CANCELLED),
                'note' => $note
            ]);

            OrderHistory::query()->create([
                'order_id' => $order->id,
                'order_status_id' => $order->order_status_id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Hủy đơn hàng thành công'
            ]);

        }catch (\Exception $exception){

            return response()->json([
            'success' => true,
            'message' => $exception->getMessage()
            ]);

        }catch (ValidationException $validationException){

            return response()->json([
                'success' => true,
                'message' => $validationException->getMessage()
            ]);

        }
    }
}
