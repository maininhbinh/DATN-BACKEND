<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'total_price',
        'note',
        'order_type',
        'order_status_id',
        'receiver_name',
        'receiver_email',
        'receiver_phone',
        'receiver_address',
        'shipping_status',
        'payment_status',
        'sku',
        'discount_code',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function status()
    {
        return $this->belongsTo(OrderStatus::class, 'status_name', 'name');
    }
    public function orderStatus()
    {
        return $this->belongsTo(OrderStatus::class);
    }

    protected $appends = ['order_status_name'];

    public function getOrderStatusNameAttribute()
    {
        return $this->orderStatus ? $this->orderStatus->name : null;
    }

    public function statusHistories()
    {
        return $this->hasMany(OrderStatusHistory::class);
    }
}
