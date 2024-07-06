<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory;
    protected $table = 'carts';

    protected $fillable = [
        'user_id',
        'product_attr_id',
        'quantity',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function productItem()
    {
        return $this->belongsTo(Product_item::class, 'product_item_id');
    }
}
