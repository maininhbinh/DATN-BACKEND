<?php
namespace App\Http\Controllers\api;

use App\Enums\OrderStatus as EnumOrderStatus;
use App\Enums\PaymentMethods;
use App\Enums\PaymentStatuses;
use App\Enums\TypeDiscounts;
use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\OrderHistory;
use App\Models\OrderStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{

    public function index()
    {
        try {

            $item = Order::orderBy('created_at', 'desc')->get();

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
                ->with(['orderDetails' => function($query){
                    $query
                        ->with(['productItem' => function ($query){
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
                    'orders.pick_up_required',
                    'orders.note',
                    'orders.sku as code',
                    'orders.created_at',
                    'payment_statuses.name as payment_status',
                    'order_status_id',
                    'order_statuses.name as order_status',
                    'payment_methods.description as payment_methods',
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
                    'order_details' => $item->orderDetails->map(function($item){
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

    public function getOrderDetail(Request $request, $id){
        try {
            $request->user();

            $orderDetail = Order::where('orders.id', $id)
                ->where('orders.user_id', $request->user()->id)
                ->with(['orderDetails' => function($query){
                    $query
                        ->with(['productItem' => function ($query){
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
                ->with(['histories' => function ($query){
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
                    'orders.receiver_pronvinces',
                    'orders.receiver_district',
                    'orders.receiver_district',
                    'orders.receiver_ward',
                    'orders.receiver_address',
                    'orders.discount_price',
                    'orders.discount_code',
                    'orders.discount_code',
                    'orders.pick_up_required',
                    'orders.note',
                    'orders.sku as code',
                    'orders.created_at',
                    'payment_statuses.name as payment_status',
                    'order_status_id',
                    'order_statuses.name as order_status',
                    'payment_methods.description as payment_methods',
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
                'receiver_pronvinces' => $orderDetail->receiver_pronvinces,
                'receiver_district' => $orderDetail->receiver_district,
                'receiver_ward' => $orderDetail->receiver_ward,
                'receiver_address' => $orderDetail->receiver_address,
                'pick_up_required' => $orderDetail->pick_up_required,
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
                'order_details' => $orderDetail->orderDetails->map(function($item){
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
                'histories' => $orderDetail->histories->map(function($history) {
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
        }catch (\Exception $exception){
            return response()->json([
                'success' => false,
                'massage' => $exception->getMessage()
            ]);
        }
    }

    public function show($id){
        try {
            $orderDetail = Order::where('orders.id', $id)
                ->with(['orderDetails' => function($query){
                    $query
                        ->with(['productItem' => function ($query){
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
                ->with(['histories' => function ($query){
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
                    'orders.receiver_pronvinces',
                    'orders.receiver_district',
                    'orders.receiver_district',
                    'orders.receiver_ward',
                    'orders.receiver_address',
                    'orders.discount_price',
                    'orders.discount_code',
                    'orders.discount_code',
                    'orders.pick_up_required',
                    'orders.note',
                    'orders.sku as code',
                    'orders.created_at',
                    'payment_statuses.name as payment_status',
                    'order_status_id',
                    'order_statuses.name as order_status',
                    'payment_methods.description as payment_methods',
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
                'receiver_pronvinces' => $orderDetail->receiver_pronvinces,
                'receiver_district' => $orderDetail->receiver_district,
                'receiver_ward' => $orderDetail->receiver_ward,
                'receiver_address' => $orderDetail->receiver_address,
                'pick_up_required' => $orderDetail->pick_up_required,
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
                'order_details' => $orderDetail->orderDetails->map(function($item){
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
                'histories' => $orderDetail->histories->map(function($history) {
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
        }catch (\Exception $exception){
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
                    'receiver_phone' => 'required|string',
                    'receiver_pronvinces' => 'required|string',
                    'receiver_district' => 'required|string',
                    'receiver_ward' => 'required|string',
                    'receiver_address' => 'required|string',
                    'pick_up_required' => 'required',
//                'payment_method_id' => 'required',
                ],
                [
                    'receiver_name' => 'Trường name là bắt buộc',
                    'receiver_name.string' => 'Trường name phải là một chuỗi',
                    'receiver_phone' => 'Trường phone là bắt buộc',
                    'receiver_phone.string' => 'Trường phone là một chuỗi',
                    'receiver_pronvinces' => 'Băt buộc chọn một tỉnh thành',
                    'receiver_district' => 'Chọn một thành phố',
                    'receiver_ward' => 'Chọn một quận | huyện',
                    'receiver_address' => 'Trường address là bắt buộc',
                    'pick_up_required' => 'Chọn hình thức nhận hàng',
//                'payment_method_id' => 'Chọn một hình thức thanh toán COD|shipment'
                ]
            );

            $receiverName = $request->get('receiver_name');
            $receiverPhone = $request->get('receiver_phone');
            $receiverPronvices = $request->get('receiver_pronvinces');
            $receiverDistrict = $request->get('receiver_district');
            $receiverWard = $request->get('receiver_ward');
            $receiverAddress = $request->get('receiver_address');
            $pickUpRequired = filter_var($request->get('pick_up_required'), FILTER_VALIDATE_BOOLEAN) ? 1 : 0;
            $note = $request->get('note');
            $discountCode = $request->get('discount_code');
            $paymentMethod = PaymentMethods::getOrder(PaymentMethods::MOMO);

            $paymentStatusId = PaymentStatuses::getOrder(PaymentStatuses::PENDING);
            $orderStatusId = EnumOrderStatus::getOrder(EnumOrderStatus::PENDING);

            $user = $request->user();

            $carts = Cart::where('user_id', $user->id)
                ->join('product_items', 'carts.product_item_id', '=', 'product_items.id')
                ->join('products', 'product_items.product_id', '=', 'products.id')
                ->select(
                    'carts.*',
                    DB::raw("
                    CASE
                        WHEN products.type_discount = '" . TypeDiscounts::Percent->value . "' THEN product_items.price * (1 - products.discount / 100)
                        WHEN products.type_discount = '" . TypeDiscounts::Fixed->value . "' THEN product_items.price - products.discount
                        ELSE product_items.price
                    END AS price
                ")
                )
                ->get();

            if(!$carts || count($carts) <= 0){
                return response()->json([
                    'sucess' => false,
                    'message' => 'Giỏ hàng ít nhất phải có 1 sản phẩm'
                ], 404);
            }

            $totalPrice = 0;

            foreach ($carts as $cart){
                $totalPrice += $cart->price * $cart->quantity;
            }

            //xử lý discount code

            //$discountPrice = $totalPrice - $discountCode;
            $discountPrice = $totalPrice;

            $order = Order::create([
                'user_id' => $user->id,
                'total_price' => $totalPrice,
                'note' => $note,
                'order_status_id' => $orderStatusId,
                'receiver_name' => $receiverName,
                'receiver_phone' => $receiverPhone,
                'receiver_pronvinces' => $receiverPronvices,
                'receiver_district' => $receiverDistrict,
                'receiver_ward' => $receiverWard,
                'receiver_address' => $receiverAddress,
                'payment_method_id' => $paymentMethod,
                'payment_status_id' => $paymentStatusId,
                'pick_up_required' => $pickUpRequired,
                'discount_code' => $discountCode,
                'discount_price' => $discountPrice,
            ]);

            OrderHistory::create([
                'order_id' => $order->id,
                'order_status_id' => EnumOrderStatus::getOrder(EnumOrderStatus::PENDING)
            ]);

            foreach ($carts as $cart) {
                OrderDetail::create([
                    'product_item_id' => $cart->product_item_id,
                    'order_id' => $order->id,
                    'quantity' => $cart->quantity,
                    'price' => $cart->price,
                ]);
            }

            Cart::where('user_id', $user->id)->delete();

            DB::commit();
            return redirect()->action([PaymentController::class, 'momo_payment'], ['orderId' => $order->id]);
        }
        catch (ValidationException $validationException){
            return response()->json([
                'success' => false,
                'massage' => $validationException->getMessage()
            ]);
        }
        catch(\Exception $exception){

            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $exception->getMessage()
            ]);

        }

    }

    public function updateStatus(Request $request, $id){
        try {

            $orderStatus = $request->input('status');

            $order = Order::findOrFail($id);
            $order->order_status_id = $orderStatus;
            $order->save();

            OrderHistory::create([
                'order_id' => $order->id,
                'order_status_id' => $orderStatus
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật trạng thái thành công'
            ]);

        }catch(\Exception $exception){
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage()
            ]);
        }
    }
}
