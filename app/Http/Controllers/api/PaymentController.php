<?php

namespace App\Http\Controllers\api;

use App\Enums\PaymentStatuses;
use App\Events\OrderCreated;
use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
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

    public function momo_payment($orderId)
    {
        try {
            $order = Order::where('id', $orderId)->first();

            if (!$order) {
                return response()->json(['message' => 'Order not found'], 404);
            }

            if ($order->payment_status_id === PaymentStatuses::getOrder(PaymentStatuses::COMPLETED)) {
                return response()->json(['message' => 'Đơn hàng đã thanh toán'], 400);
            }

//            $endpoint = env('MOMO_PAYMENT_URL');
//            $partnerCode = env('MOMO_PAYMENT_PARTNER_CODE');
//            $accessKey = env('MOMO_PAYMENT_ACCESS_KEY');
//            $secretKey = env('MOMO_PAYMENT_SECRET_KEY');
//            $orderInfo = "Thanh toán qua MoMo";
//            $amount = $order->total_price;
//            $returnUrl = env('MOMO_PAYMENT_RETURN_URL');
//            $notifyurl = env('MOMO_PAYMENT_NOTIFY_URL');

            $endpoint = 'https://test-payment.momo.vn/gw_payment/transactionProcessor';
            $partnerCode = 'MOMOBKUN20180529';
            $accessKey = 'klm05TvNBzhg7h7j';
            $secretKey = 'at67qH6mk8w5Y1nAyMoYKMWACiEi2bsa';
            $orderInfo = "Thanh toán qua MoMo";
            $amount = $order->total_price;
            $returnUrl = 'http://127.0.0.1:8000/api/payment/callback';
            $notifyurl = 'http://localhost:8000/atm/ipn_momo.php';

            $bankCode = "SML";
            $orderid = strval($order->sku);
            $requestId = time() . "";
            $requestType = "payWithMoMoATM";
            $extraData = "";
            $rawHash = "partnerCode=$partnerCode&accessKey=$accessKey&requestId=$requestId&bankCode=$bankCode&amount=$amount&orderId=$orderid&orderInfo=$orderInfo&returnUrl=$returnUrl&notifyUrl=$notifyurl&extraData=$extraData&requestType=$requestType";
            $signature = hash_hmac("sha256", $rawHash, $secretKey);
            $data =  [
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
            ];

            $result = $this->execPostRequest($endpoint, json_encode($data));
            $jsonResult = json_decode($result, true);

            if (isset($jsonResult['payUrl'])) {

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
                    $order = Order::where('sku', $orderId)
                        ->join('users', 'orders.user_id', '=', 'users.id')
                        ->select('orders.*', 'users.email')
                        ->first();

                    if (!$order) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Order not found'
                        ], 404);
                    }

                    Order::where('sku', $orderId)->update([
                        'payment_status_id' => PaymentStatuses::getOrder(PaymentStatuses::COMPLETED)
                    ]);

                    $status = PaymentStatuses::COMPLETED->value;

                    event(new OrderCreated($order, $status, $order->email));

                    return redirect('http://localhost:5173/');
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
