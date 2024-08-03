<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Stripe\StripeClient;
use Exception;
use Illuminate\Http\Request;

class StripeController extends Controller
{
    public function stripePayment(Request $request, $order_id)
    {
        $order = Order::find($order_id);

        if ($order) {
            try {
                $cardInfo = [
                    'number' => $request->input('card_number'),
                    'exp_month' => $request->input('card_exp_month'),
                    'exp_year' => $request->input('card_exp_year'),
                    'cvc' => $request->input('card_cvc'),
                ];

                if (!$order->total_price || !is_numeric($order->total_price) || $order->total_price <= 0) {
                    return response()->json(['message' => 'Invalid total amount'], 400);
                }

                $stripe = new StripeClient('sk_test_BQokikJOvBiI2HlWgH4olfQ2');

                $token = $stripe->tokens->create(['card' => $cardInfo]);

                $charge = $stripe->charges->create([
                    'amount' => $order->discount_price , // Convert to cents
                    'currency' => 'vnd',
                    'source' => $token->id,
                    'description' => 'Order payment for order ID: ' . $order->id,
                ]);

                $order->payment_status_id = 2; //status: Đã thanh toán
                $order->payment_method_id = 1; // Payment Method: Stripe
                $order->save();

                return response()->json(['message' => 'Success', 'order' => $order], 201);
            } catch (\Stripe\Exception\CardException $e) {
                return response()->json(['message' => 'Card Error', 'errors' => $e->getMessage()], 400);
            } catch (Exception $e) {
                return response()->json(['message' => 'Errors', 'errors' => $e->getMessage()], 500);
            }
        } else {
            return response()->json(['message' => 'Order not found'], 404);
        }
    }
}
