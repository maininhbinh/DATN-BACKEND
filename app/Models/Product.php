<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    const TYPE_DISCOUNT = [
        'PERCENTAGE',
        'FIXED PRICE'
    ];

    protected $fillable = [
        'category_id',
        'thumbnail',
        'name',
        'description',
        'discount',
        'type_discount',
        'brand_id',
        'total_review',
        'avg_stars',
        'active'
    ];
}
