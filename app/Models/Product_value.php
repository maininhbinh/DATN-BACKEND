<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product_value extends Model
{
    use HasFactory;
    protected $table = 'product_values';
    protected $fillable = [
        'product_id',
        'value_id'
    ];
}
