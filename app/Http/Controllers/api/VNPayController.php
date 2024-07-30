<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class VNPayController extends Controller
{
    public function vnpay_payment(Request $request, $order_id)
    {
        $order = Order::find($order_id);
        if ($order->payment_status_id == 2) {
            return response()->json(['message' => 'Đơn hàng đã thanh toán'], 404);
        } else {
            $vnp_Url = "https://sandbox.vnpayment.vn/paymentv2/vpcpay.html";
            $vnp_Returnurl = url('/vnpay-return'); // Set your actual return URL here
            $vnp_TmnCode = env('VNPAY_TMN_CODE'); // Set in .env file
            $vnp_HashSecret = env('VNPAY_HASH_SECRET'); // Set in .env file

            $vnp_TxnRef = $order->order_code;
            $vnp_OrderInfo = "Thanh toán đơn hàng";
            $vnp_OrderType = "billpayment";
            $vnp_Amount = $order->total_price * 100;
            $vnp_Locale = "vn";
            $vnp_BankCode = "NCB";
            $vnp_IpAddr = $_SERVER['REMOTE_ADDR'];

            // Assuming $fullName is a parameter in the request
            $fullName = $request->input('fullName', '');
            if (!empty($fullName)) {
                $name = explode(' ', $fullName);
                $vnp_Bill_FirstName = array_shift($name);
                $vnp_Bill_LastName = array_pop($name);
            }

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
            $hashdata = "";
            foreach ($inputData as $key => $value) {
                $hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
                $query .= urlencode($key) . "=" . urlencode($value) . '&';
            }
            $vnp_Url .= "?" . $query;
            if (isset($vnp_HashSecret)) {
                $vnpSecureHash = hash_hmac('sha512', $hashdata, $vnp_HashSecret);
                $vnp_Url .= 'vnp_SecureHash=' . $vnpSecureHash;
            }

            $returnData = array(
                'code' => '00', 'message' => 'success', 'data' => $vnp_Url,
            );
            $order->payment_status_id = 2; //status: Đã thanh toán
            $order->payment_method_id = 3; // Payment Method: VNPay
            $order->save();
            return response()->json(["data" => $returnData, "order" => $order]);
        }
    }

    public function returnCallBack(Request $request)
    {
        $vnp_HashSecret = env('VNPAY_HASH_SECRET'); // Set in .env file
        $inputData = $request->except('vnp_SecureHash');
        ksort($inputData);
        $hashData = "";
        $i = 0;
        foreach ($inputData as $key => $value) {
            $hashData .= ($i == 0 ? '' : '&') . urlencode($key) . "=" . urlencode($value);
            $i = 1;
        }

        $secureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);

        return view('vnpay_response', [
            'vnp_TxnRef' => $request->input('vnp_TxnRef'),
            'vnp_Amount' => $request->input('vnp_Amount'),
            'vnp_OrderInfo' => $request->input('vnp_OrderInfo'),
            'vnp_ResponseCode' => $request->input('vnp_ResponseCode'),
            'vnp_TransactionNo' => $request->input('vnp_TransactionNo'),
            'vnp_BankCode' => $request->input('vnp_BankCode'),
            'vnp_PayDate' => $request->input('vnp_PayDate'),
            'secureHash' => $secureHash,
            'vnp_SecureHash' => $request->input('vnp_SecureHash'),
        ]);
    }
}
