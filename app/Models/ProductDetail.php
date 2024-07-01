<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductDetail extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'product_details';
    protected $fillable = [
        'product_id', 'detail_id'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function detail()
    {
        return $this->hasMany(Detail::class);
    }
}
