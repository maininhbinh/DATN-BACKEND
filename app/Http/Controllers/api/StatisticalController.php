<?php

namespace App\Http\Controllers\api;

use App\Enums\PaymentStatuses;
use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StatisticalController extends Controller
{
    //
    public function today(Request $request){
        try {
            $totalSalesToday  = Order::select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(total_price) as total_sales'))
                ->whereDate('created_at', Carbon::today())
                ->where('payment_status_id', PaymentStatuses::getOrder(PaymentStatuses::COMPLETED))
                ->orderBy('date', 'asc')
                ->sum('total_price');
            $totalSalesYesterday = Order::select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(total_price) as total_sales'))
                ->whereDate('created_at', Carbon::yesterday())
                ->where('payment_status_id', PaymentStatuses::getOrder(PaymentStatuses::COMPLETED))
                ->orderBy('date', 'asc')
                ->sum('total_price');

            if ($totalSalesYesterday > 0) {
                $percentSale = (($totalSalesToday - $totalSalesYesterday) / $totalSalesYesterday) * 100;
            } else {
                $percentSale = $totalSalesToday > 0 ? 100 : 0; // Xử lý trường hợp không có đơn hàng hôm qua
            }

            $salesByDay = [
                'title' => $totalSalesToday,
                'percent' => ceil($percentSale),
            ];


            $totalOrderByDay = Order::select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(total_price) as total_sales'))
                ->whereDate('created_at', Carbon::today())
                ->orderBy('date', 'asc')
                ->sum('total_price');
            $totalOrderYesterday = Order::select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(total_price) as total_sales'))
                ->whereDate('created_at', Carbon::yesterday())
                ->orderBy('date', 'asc')
                ->sum('total_price');

            if ($totalOrderYesterday > 0) {
                $percentOrder = (($totalOrderByDay - $totalOrderYesterday) / $totalOrderYesterday) * 100;
            } else {
                $percentOrder = $totalOrderByDay > 0 ? 100 : 0; // Xử lý trường hợp không có đơn hàng hôm qua
            }

            $orderByDay = [
                'title' => $totalOrderByDay,
                'percent' => ceil($percentOrder),
            ];

            $totalUserByDay = User::whereDate('created_at', Carbon::today())->count();
            $totalUserYesterday = User::whereDate('created_at', Carbon::yesterday())->count();

            if ($totalUserYesterday > 0) {
                $percentUser = (($totalUserByDay - $totalUserYesterday) / $totalUserYesterday) * 100;
            } else {
                $percentUser = $totalUserByDay > 0 ? 100 : 0; // Xử lý trường hợp không có đơn hàng hôm qua
            }

            $userByDay = [
                'title' => $totalUserByDay,
                'percent' => ceil($percentUser),
            ];

            $totalCouponByDay = Coupon::whereDate('created_at', Carbon::today())->count();
            $totalCouponYesterday = Coupon::whereDate('created_at', Carbon::yesterday())->count();

            if ($totalCouponYesterday > 0) {
                $percentCoupon = (($totalCouponByDay - $totalCouponYesterday) / $totalCouponYesterday) * 100;
            } else {
                $percentCoupon = $totalCouponByDay > 0 ? 100 : 0; // Xử lý trường hợp không có đơn hàng hôm qua
            }

            $couponByDay = [
                'title' => $totalCouponByDay,
                'percent' => ceil($percentCoupon),
            ];

            return response()->json([
                'success' => true,
                'salesByDay' => $salesByDay,
                'orderByDay' => $orderByDay,
                'userByDay' => $userByDay,
                'totalCoupon' => $couponByDay
            ]);
        }catch (\Exception $exception){
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage()
            ]);
        }
    }
}
