<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $item = Order::orderBy('created_at', 'desc')->get();
            return response()->json($item, 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e
            ], 500);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    // public function store(Request $request)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'total_price' => 'required|numeric',
    //         'order_type' => 'required|string',
    //         'receiver_name' => 'required|string',
    //         'receiver_email' => 'required|string|email',
    //         'receiver_phone' => 'required|string',
    //         'receiver_address' => 'required|string',
    //         'shipping_status' => 'string',
    //         'payment_status' => 'required|string',
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json(['message' => $validator->errors()], 400);
    //     }

    //     try {
    //         $orderData = $request->all();
    //         if (Auth::check()) {
    //             $orderData['user_id'] = Auth::id();
    //         } else {
    //             $orderData['user_id'] = null;
    //         }

    //         $item = Order::create($orderData);
    //         return response()->json($item, 201);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => $e
    //         ], 500);
    //     }
    // }
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'total_price' => 'required|numeric',
            'order_type' => 'required|string',
            'receiver_name' => 'required|string',
            'receiver_email' => 'required|string|email',
            'receiver_phone' => 'required|string',
            'receiver_address' => 'required|string',
            'shipping_status' => 'string',
            'payment_status' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()], 400);
        }

        try {
            $orderData = $request->all();
            if (Auth::check()) {
                $orderData['user_id'] = Auth::id();
            } else {
                $orderData['user_id'] = null;
            }

            // Nếu order_status_id không tồn tại trong request, sử dụng giá trị mặc định là 1
            $orderStatusId = $request->order_status_id ?? 1;
            $orderStatus = OrderStatus::findOrFail($orderStatusId);
            $orderData['order_status_id'] = $orderStatusId;
            $orderData['order_status_name'] = $orderStatus->name;

            $item = Order::create($orderData);
            return response()->json($item, 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi khi tạo đơn hàng: ' . $e->getMessage()
            ], 500);
        }
    }


    /**
     * Display the specified resource.
     */
    public function edit(string $id)
    {
        try {
            $item = Order::findOrFail($id);
            return response()->json($item, 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy đơn hàng.'
            ], 404);
        }
    }

    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'total_price' => 'required|numeric',
            'order_type' => 'required|string',
            'receiver_name' => 'required|string',
            'receiver_email' => 'required|string|email',
            'receiver_phone' => 'required|string',
            'receiver_address' => 'required|string',
            'shipping_status' => 'string',
            'payment_status' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()], 400);
        }

        try {
            $item = Order::findOrFail($id);
            $item->update($request->all());
            return response()->json($item);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi khi cập nhật hoá đơn.'
            ], 500);
        }
    }
}
