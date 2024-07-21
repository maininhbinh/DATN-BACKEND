<?php
namespace App\Http\Controllers\api;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethods;
use App\Enums\PaymentStatuses;
use App\Enums\TypeDiscounts;
use App\Helpers\AuthHelpers;
use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\OrderHistory;
use App\Models\StatusHistory;
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
    public function show(Request $request)
    {
        try {

            $token = $request->bearerToken();
            $user = AuthHelpers::CheckAuth($token);

            if($user && $user->id){

                $item = Order::where('user_id', $user->id)->orderBy('created_at', 'desc')->get();

                return response()->json([
                    'sucess' => true,
                    'data' => $item
                ], 200);

            }

            return response()->json([]);

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => $e
            ], 500);

        }
    }

    public function placeOrder(Request $request)
    {
        try {
            DB::beginTransaction();

            $request->validate(
                [
                    'receiver_name' => 'required|string',
                    'receiver_email' => 'required|string|email',
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
                    'receiver_email' => 'Trường email là bắt buộc',
                    'receiver_email.email' => 'Trường email phải định dạng là email',
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
            $receiverEmail = $request->get('receiver_email');
            $receiverPhone = $request->get('receiver_phone');
            $receiverPronvices = $request->get('receiver_pronvinces');
            $receiverDistrict = $request->get('receiver_district');
            $receiverWard = $request->get('receiver_ward');
            $receiverAddress = $request->get('receiver_address');
            $pickUpRequired = filter_var($request->get('pick_up_required'), FILTER_VALIDATE_BOOLEAN) ? 1 : 0;
            $note = $request->get('note');
            $discountCode = $request->get('discount_code');
            $paymentMethod = 1;

            $paymentStatusId = PaymentStatuses::getOrder(PaymentStatuses::PENDING);
            $orderStatusId = OrderStatus::getOrder(OrderStatus::PENDING);

            $token = $request->bearerToken();
            $user = AuthHelpers::CheckAuth($token);

            if($user && $user->id){

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
                    $totalPrice += $cart->price;
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
                    'receiver_email' => $receiverEmail,
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
                   'order_status_id' => OrderStatus::getOrder(OrderStatus::PENDING)
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
}
