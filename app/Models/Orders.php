<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Orders extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'total_price',
        'note',
        'order_type',
        'status',
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
}
