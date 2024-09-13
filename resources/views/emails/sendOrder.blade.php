<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>ORDER</title>
</head>
<body>
<section class="h-100 gradient-custom">
    <div class="container py-5 h-100">
        <div class="row d-flex justify-content-center align-items-center h-100">
            <div class="col-lg-10 col-xl-8">
                <div class="card" style="border-radius: 10px;">
                    <div class="card-header px-4 py-5">
                        <h5 class="text-muted mb-0">Cảm ơn bạn đã đặt hàng, <span style="color: #a8729a;">Tech entry</span>!</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <p class="lead fw-normal mb-0" style="color: #a8729a;">Receipt</p>
{{--                            @if(isset($order->discount_code))--}}
{{--                                <p class="small text-muted mb-0">Mã khuyễn mãi đã áp dụng : $order->discount_code</p>--}}
{{--                            @endif--}}
                        </div>
{{--                        @foreach($order->order_details as $order_detail)--}}
{{--                            <div class="card shadow-0 border mb-4">--}}
{{--                                <div class="card-body">--}}
{{--                                    <div class="row">--}}
{{--                                        <div class="col-md-2">--}}
{{--                                            <img src="{{$order_detail->thumbnail}}"--}}
{{--                                                 class="img-fluid" alt="Phone">--}}
{{--                                        </div>--}}
{{--                                        <div class="col-md-2 text-center d-flex justify-content-center align-items-center">--}}
{{--                                            <p class="text-muted mb-0">{{$order_detail->name}}</p>--}}
{{--                                        </div>--}}
{{--                                        @foreach($order_detail->variants as $variant)--}}
{{--                                            <div class="col-md-2 text-center d-flex justify-content-center align-items-center">--}}
{{--                                                <p class="text-muted mb-0 small">{{$variant->name}}</p>--}}
{{--                                            </div>--}}
{{--                                        @endforeach--}}
{{--                                        <div class="col-md-2 text-center d-flex justify-content-center align-items-center">--}}
{{--                                            <p class="text-muted mb-0 small">Qty: {{$order_detail->quantity}}</p>--}}
{{--                                        </div>--}}
{{--                                        <div class="col-md-2 text-center d-flex justify-content-center align-items-center">--}}
{{--                                            <p class="text-muted mb-0 small">{{$order_detail->price}}</p>--}}
{{--                                        </div>--}}
{{--                                    </div>--}}
{{--                                    <hr class="mb-4" style="background-color: #e0e0e0; opacity: 1;">--}}
{{--                                </div>--}}
{{--                            </div>--}}
{{--                        @endforeach--}}
{{--                        <div class="d-flex justify-content-between pt-2">--}}
{{--                            <p class="fw-bold mb-0">Chi tiết đơn hàngs</p>--}}
{{--                            <p class="text-muted mb-0"><span class="fw-bold me-4">tổng</span> {{$order->$order_detail->total_price}}</p>--}}
{{--                        </div>--}}

{{--                        <div class="d-flex justify-content-between pt-2">--}}
{{--                            <p class="text-muted mb-0">Đơn hàng số : {{$order->$order_detail->id}}</p>--}}
{{--                            <p class="text-muted mb-0"><span class="fw-bold me-4">Giá khuyến mãi</span> {{$order->$order_detail->discount_price}}</p>--}}
{{--                        </div>--}}

{{--                        <div class="d-flex justify-content-between mb-5">--}}
{{--                            <p class="text-muted mb-0">Mã đơn hàng : {{$order->$order_detail->sku}}</p>--}}
{{--                        </div>--}}
                    </div>
                    <div class="card-footer border-0 px-4 py-5"
                         style="background-color: #a8729a; border-bottom-left-radius: 10px; border-bottom-right-radius: 10px;">
                        <h5 class="d-flex align-items-center justify-content-end text-white text-uppercase mb-0">Tổng đã thanh toán: <span class="h2 mb-0 ms-2">{{$order->$order_detail->total_price}}</span></h5>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
</body>
</html>
