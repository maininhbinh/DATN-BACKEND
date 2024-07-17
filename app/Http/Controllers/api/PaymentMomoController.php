<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Mail\OrderSuccess;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentMomoController extends Controller
{
    public function execPostRequest($url, $data)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt(
            $ch,
            CURLOPT_HTTPHEADER,
            array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($data)
            )
        );
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }

    public function momo_payment($order_id)
    {
        try {
            $order = Order::find($order_id);

            if (!$order) {
                return response()->json(['message' => 'Order not found'], 404);
            }

            if ($order->payment === "Paid") {
                return response()->json(['message' => 'Đơn hàng đã thanh toán'], 400);
            }

            $endpoint = "https://test-payment.momo.vn/gw_payment/transactionProcessor";
            $partnerCode = "MOMOBKUN20180529";
            $accessKey = "klm05TvNBzhg7h7j";
            $secretKey = "at67qH6mk8w5Y1nAyMoYKMWACiEi2bsa";
            $orderInfo = "Thanh toán qua MoMo";
            $amount = strval($order->total_price);
            $returnUrl = "http://127.0.0.1:8000/momo-response";
            $notifyurl = "http://localhost:8000/atm/ipn_momo.php";
            $bankCode = "SML";
            $orderid = strval($order->id);
            $requestId = time() . "";
            $requestType = "payWithMoMoATM";
            $extraData = "";
            $rawHash = "partnerCode=$partnerCode&accessKey=$accessKey&requestId=$requestId&bankCode=$bankCode&amount=$amount&orderId=$orderid&orderInfo=$orderInfo&returnUrl=$returnUrl&notifyUrl=$notifyurl&extraData=$extraData&requestType=$requestType";
            $signature = hash_hmac("sha256", $rawHash, $secretKey);
            $data =  array(
                'partnerCode' => $partnerCode,
                'accessKey' => $accessKey,
                'requestId' => $requestId,
                'amount' => $amount,
                'orderId' => $orderid,
                'orderInfo' => $orderInfo,
                'returnUrl' => $returnUrl,
                'bankCode' => $bankCode,
                'notifyUrl' => $notifyurl,
                'extraData' => $extraData,
                'requestType' => $requestType,
                'signature' => $signature
            );

            $result = $this->execPostRequest($endpoint, json_encode($data));
            $jsonResult = json_decode($result, true);

            if (isset($jsonResult['payUrl'])) {
                $order->payment_url = $jsonResult['payUrl'];
                $order->save();
                return response()->json(['url' => $jsonResult['payUrl']], 200);
            } else {
                return response()->json(['message' => 'Error generating payment URL'], 500);
            }
        } catch (\Exception $e) {
            Log::error('PaymentMomoController::momo_payment - Error: ' . $e->getMessage());
            return response()->json(['message' => 'Internal server error'], 500);
        }
    }

    public function fallBack(Request $request)
    {
        try {
            $secretKey = 'at67qH6mk8w5Y1nAyMoYKMWACiEi2bsa';
            $partnerCode = $request->query('partnerCode');
            $accessKey = $request->query('accessKey');
            $orderId = $request->query('orderId');
            $order = Order::find($orderId);
            $localMessage = $request->query('localMessage');
            $message = $request->query('message');
            $transId = $request->query('transId');
            $orderInfo = $request->query('orderInfo');
            $amount = $request->query('amount');
            $errorCode = $request->query('errorCode');
            $responseTime = $request->query('responseTime');
            $requestId = $request->query('requestId');
            $extraData = $request->query('extraData');
            $payType = $request->query('payType');
            $orderType = $request->query('orderType');
            $m2signature = $request->query('signature');

            $rawHash = "partnerCode=$partnerCode&accessKey=$accessKey&requestId=$requestId&amount=$amount&orderId=$orderId&orderInfo=$orderInfo&orderType=$orderType&transId=$transId&message=$message&localMessage=$localMessage&responseTime=$responseTime&errorCode=$errorCode&payType=$payType&extraData=$extraData";
            $partnerSignature = hash_hmac("sha256", $rawHash, $secretKey);

            if ($m2signature == $partnerSignature) {
                if ($errorCode == '0') {
                    $order->update([
                        'payment' => 'Paid',
                        'payment_url' => '',
                    ]);

                    $orderDetail = Order::where('order_id', $order->id)->get();
                    $user = User::find($order->user_id);
                    $trangThai = "Đã thanh toán";
                    Mail::to($order->email)->send(new OrderSuccess($order, $orderDetail, $trangThai));

                    return response()->json(['message' => 'Payment success'], 200);
                } else {
                    return response()->json(['message' => $message . '/' . $localMessage], 400);
                }
            } else {
                return response()->json(['message' => 'This transaction could be hacked, please check your signature and returned signature'], 400);
            }
        } catch (\Exception $e) {
            Log::error('PaymentMomoController::fallBack - Error: ' . $e->getMessage());
            return response()->json(['message' => 'Internal server error'], 500);
        }
    }
}
