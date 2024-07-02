<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product_detail extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'product_details';

    protected $fillable = [
        'product_id',
        'value_id'
    ];

}
