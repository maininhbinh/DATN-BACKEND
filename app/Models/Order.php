<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'orders';

    protected $fillable = [
        'user_id',
        'total_price',
        'order_status_id',
        'receiver_name',
        'receiver_phone',
        'receiver_pronvinces',
        'receiver_district',
        'receiver_ward',
        'receiver_address',
        'payment_status',
        'payment_method_id',
        'pick_up_required',
        'discount_price',
        'discount_code',
        'note',
        'sku',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function orderStatus()
    {
        return $this->belongsTo(OrderStatus::class);
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function paymentStatus()
    {
        return $this->belongsTo(PaymentStatus::class);
    }

    public function orderDetails(){
        return $this->hasMany(OrderDetail::class);
    }

    public function histories(){
        return $this->hasMany(OrderHistory::class);
    }

    protected static function booted()
    {
        static::creating(function ($order) {
            do {
                $sku = 'ORDER-' . strtoupper(Str::random(8));
            } while (Order::where('sku', $sku)->exists());

            $order->sku = $sku;
        });
    }
    public function coupon()
    {
        return $this->belongsTo(Coupon::class);
    }
}
