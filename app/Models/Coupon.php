<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Coupon extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'coupons';
    protected $fillable = [
        'name',
        'code',
        'quantity',
        'value',
        'type',
        'start_date',
        'end_date',
        'used_count',
        'is_activate',
        'discount_max',
        'status',
    ];

    public function getCouponValue(): string
    {
        if ($this->type === "percent") {
            return "%";
        } else {
            return "VNĐ";
        }
    }

    public function qualified($userPoint)
    {
        return $userPoint >= $this->point_required;
    }
}
