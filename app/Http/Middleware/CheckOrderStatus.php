<?php

namespace App\Http\Middleware;

use App\Models\Order;
use App\Models\OrderStatus;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckOrderStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $orderStatus = Order::find($request->route('id'));
        if($orderStatus->order_status_id >= $request->input('status')){
            return response()->json(['error' => 'Không thể cập nhật về trạng thái trước đó'], 400);
        }
        return $next($request);
    }
}
