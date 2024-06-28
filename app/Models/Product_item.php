<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product_item extends Model
{
    use HasFactory;

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

    public function product(){
        return $this->belongsTo(Product::class);
    }

    public function variants(){
        return $this->belongsToMany(VariantOption::class, 'product_configurations');
    }

}
