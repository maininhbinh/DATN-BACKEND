<?php

namespace App\Http\Controllers\api;

use App\Enums\PaymentStatuses;
use App\Events\OrderCreated;
use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class VnPayController extends Controller
{
    public function vnpayPayment(Request $request, $order_id)
    {
        $order = Order::find($order_id);

        if ($order->payment_status_id == 2) {
            return response()->json(['message' => 'Đơn hàng đã thanh toán'], 404);
        }

        $vnp_Url = "https://sandbox.vnpayment.vn/paymentv2/vpcpay.html";
        $vnp_Returnurl = 'http://127.0.0.1:8000/api/payment/vnpay_callback'; // Set your actual return URL here
        $vnp_TmnCode = "YKQE9ZZ7"; // Set in .env file
        $vnp_HashSecret = "6FVJRRE8PB3R9GRJNLFGDUIWVCEEO547"; // Set in .env file

        $vnp_TxnRef = $order->sku;
        $vnp_OrderInfo = "Thanh toán đơn hàng";
        $vnp_OrderType = "billpayment";
        $vnp_Amount = $order->total_price * 100;
        $vnp_Locale = "vn";
        $vnp_BankCode = "NCB";
        $vnp_IpAddr = $_SERVER['REMOTE_ADDR'];

        // Assuming $fullName is a parameter in the reques

        $inputData = array(
            "vnp_Version" => "2.1.0",
            "vnp_TmnCode" => $vnp_TmnCode,
            "vnp_Amount" => $vnp_Amount,
            "vnp_Command" => "pay",
            "vnp_CreateDate" => date('YmdHis'),
            "vnp_CurrCode" => "VND",
            "vnp_IpAddr" => $vnp_IpAddr,
            "vnp_Locale" => $vnp_Locale,
            "vnp_OrderInfo" => $vnp_OrderInfo,
            "vnp_OrderType" => $vnp_OrderType,
            "vnp_ReturnUrl" => $vnp_Returnurl,
            "vnp_TxnRef" => $vnp_TxnRef
        );

        if (!empty($vnp_BankCode)) {
            $inputData['vnp_BankCode'] = $vnp_BankCode;
        }

        ksort($inputData);
        $query = "";
        $i = 0;
        $hashdata = "";
        foreach ($inputData as $key => $value) {
            if($i == 1){
                $hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
            }else{
                $hashdata .= urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
            $query .= urlencode($key) . "=" . urlencode($value) . '&';
        }
        $vnp_Url .= "?" . $query;
        if (isset($vnp_HashSecret)) {
            $vnpSecureHash = hash_hmac('sha512', $hashdata, $vnp_HashSecret);
            $vnp_Url .= 'vnp_SecureHash=' . $vnpSecureHash;
        }

        return response()->json(['url' => $vnp_Url], 200);
    }

    public function returnCallBack(Request $request)
    {
        $vnpResponseCode = $request->input('vnp_ResponseCode');
        $vnpTransactionStatus = $request->input('vnp_TransactionStatus');
        $vnpAmount = $request->input('vnp_Amount');
        $vnpTxnRef = $request->input('vnp_TxnRef');
        $vnpSecureHash = $request->input('vnp_SecureHash');

        // Check Secure Hash
        $vnpData = $request->except('vnp_SecureHash');
        ksort($vnpData);
        $query = http_build_query($vnpData);
        $secureHash = hash_hmac('sha512', $query, '6FVJRRE8PB3R9GRJNLFGDUIWVCEEO547');

        if ($secureHash === $vnpSecureHash) {
            if ($vnpResponseCode === '00' && $vnpTransactionStatus === '00') {
                // Thanh toán thành công
                $order = Order::where('sku', $vnpTxnRef)
                    ->join('users', 'orders.user_id', '=', 'users.id')
                    ->select('orders.*', 'users.email')
                    ->first();


                if (!$order) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Order not found'
                    ], 404);
                }

                Order::where('sku', $vnpTxnRef)->update([
                    'payment_status_id' => PaymentStatuses::getOrder(PaymentStatuses::COMPLETED)
                ]);

//                event(new OrderCreated($orderDetail, $order->email));

                return redirect('http://localhost:5173/account/my-order/detail/'. $order->id);
            } else {
                // Thanh toán thất bại
                return redirect('http://localhost:5173/');
            }
        } else {
            // Xác thực hash không đúng
            return redirect('http://localhost:5173/');
        }
    }
}
