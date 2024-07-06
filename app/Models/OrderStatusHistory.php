<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderStatusHistory extends Model
{
    use HasFactory;
    protected $table = 'order_status_histories';
    protected $fillable = ['order_id', 'order_status_id'];

    // Định nghĩa mối quan hệ với OrderStatus
    public function status()
    {
        return $this->belongsTo(OrderStatus::class, 'order_status_id');
    }
}
