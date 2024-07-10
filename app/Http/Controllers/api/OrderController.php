<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\OrderStatusHistory;
use Carbon\Carbon;
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
        $user = Auth::user();
        try {
            $item = Order::orderBy('created_at', 'desc')->get();
            if ($user->role_id == 1 || $user->role_id == 2) {
                $item = Order::orderBy('created_at', 'desc')->get();
            } else {
                $item = Order::where('user_id', $user->id)->orderBy('created_at', 'desc')->get();
            }
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
            OrderStatusHistory::create(
                [
                    'order_id' => $item->id,
                    'status' => $item->order_status_name,
                    'timestamp' => Carbon::now()
                ]
            );
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

            $statuses = OrderStatus::pluck('id')->toArray();
            $cancelledStatusId = OrderStatus::where('name', 'Cancelled')->first()->id;
            $currentStatusId = $item->order_status_id;
            $newStatusId = $request->order_status_id;

            if (!in_array($newStatusId, $statuses)) {
                return response()->json(['message' => 'Trạng thái không hợp lệ'], 400);
            }

            $currentIndex = array_search($currentStatusId, $statuses);
            $newIndex = array_search($newStatusId, $statuses);

            if ($newStatusId != $currentStatusId && $newStatusId != $cancelledStatusId && $newIndex !== $currentIndex + 1) {
                return response()->json(['message' => 'Trạng thái không hợp lệ. Vui lòng cập nhật theo thứ tự.'], 400);
            }

            $item->update($request->all());

            if ($newStatusId != $currentStatusId) {
                $item->order_status_id = $newStatusId;
                $item->save();

                OrderStatusHistory::create([
                    'order_id' => $item->id,
                    'status' => OrderStatus::find($newStatusId)->name, // lấy tên trạng thái mới
                    'timestamp' => Carbon::now()
                ]);
            }

            return response()->json($item);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi khi cập nhật hoá đơn.'
            ], 500);
        }
    }
    public function getOrderStatusHistory($orderId)
    {
        try {
            $orderStatusHistory = OrderStatusHistory::where('order_id', $orderId)->get();

            if ($orderStatusHistory->isEmpty()) {
                return response()->json(['message' => 'Không có lịch sử trạng thái cho đơn hàng này.'], 404);
            }

            return response()->json($orderStatusHistory);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi khi lấy lịch sử trạng thái đơn hàng.'
            ], 500);
        }
    }
}
