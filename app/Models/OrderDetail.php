<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderDetail extends Model
{
    use HasFactory;

    protected $table = 'order_details';

    protected $fillable = [
        'product_item_id',
        'order_id',
        'quantity',
        'price'
    ];

    public function productItem(){
        return $this->belongsTo(ProductItem::class);
    }

}
