<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product_configuration extends Model
{
    use HasFactory;

    protected $table = 'product_configurations';

    protected $fillable = [
        'product_item_id',
        'variant_option_id'
    ];
}
