<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductsParameter extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = ['product_id', 'parameter_id', 'name'];

    // chua co bang product
    // public function product()
    // {
    //     return $this->belongsTo(Product::class, 'product_id');
    // }

    // Quan hệ con
    public function parameter()
    {
        return $this->hasMany(Parameter::class, 'parameter_id');
    }
}
