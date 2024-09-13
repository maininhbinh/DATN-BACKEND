<?php

namespace App\Http\Controllers\api;

use App\Enums\PaymentMethods;
use App\Enums\PaymentStatuses;
use App\Http\Controllers\Controller;
use App\Models\Order;

class PaymentController extends Controller
{
    //
    public function payment($orderId){
        try {
            $order = Order::where('orders.id', $orderId)
                ->join('payment_methods', 'orders.payment_method_id', '=', 'payment_methods.id')
                ->select('orders.*', 'payment_methods.code')
                ->first();

            if (!$order) {
                return response()->json(['message' => 'Order not found'], 404);
            }

            if ($order->payment_status_id === PaymentStatuses::getOrder(PaymentStatuses::COMPLETED)) {
                return response()->json(['message' => 'Đơn hàng đã thanh toán'], 400);
            }

            switch ($order->code) {
                case PaymentMethods::VNPAY->value:
                    return redirect()->action([VnPayController::class, 'vnpayPayment'], ['orderId' => $order->id]);

                case PaymentMethods::MOMO->value:
                    return redirect()->action([MomoController::class, 'momoPayment'], ['orderId' => $order->id]);

                case PaymentMethods::COD->value:
                    return response()->json([
                        'success' => true,
                        'url' => 'http://localhost:5173/account/my-order/detail/'. $order->id
                    ]);
                default:
                    return response()->json([
                        'success' => false,
                        'url' => 'http://localhost:5173/account/my-order/detail/'. $order->id,
                        'message' => 'Phương thức thanh toán không hợp lệ hoặc không hoạt động. Vui lòng chọn phương thức khác',
                    ]);
            }
        }catch (\Exception $exception){
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage()
            ]);
        }
    }
}
