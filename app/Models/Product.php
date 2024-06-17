<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'category_id',
        'thumbnail',
        'name',
        'description',
        'discount',
        'total_review',
        'brand_id',
        'avg_stars',
        'in_active'
    ];
    use HasFactory;
}
