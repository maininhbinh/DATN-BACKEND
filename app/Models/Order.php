<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'orders';

    protected $fillable = [
        'user_id',
        'total_price',
        'status_id',
        'receiver_name',
        'receiver_email',
        'receiver_phone',
        'receiver_city',
        'receiver_county',
        'receiver_district',
        'receiver_address',
        'payment_status',
        'payment_method_id',
        'pick_up_required',
        'discount_price',
        'discount_code',
        'note',
        'sku',
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function status(){
        return $this->belongsTo(Status::class);
    }

    public function paymentMethod(){
        return $this->belongsTo(PaymentMethod::class);
    }
}
