<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'product_items';

    protected $fillable = [
        'product_id',
        'price',
        'price_sale',
        'quantity',
        'sku',
        'image',
        'public_id',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function variants(){
        return $this->belongsToMany(VariantOption::class, 'product_configurations')->withPivot('id');
    }

    public function carts()
    {
        return $this->hasMany(Cart::class, 'product_item_id');
    }

}
