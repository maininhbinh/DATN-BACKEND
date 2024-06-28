<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product_detail extends Model
{
    use HasFactory;

    protected $table = 'product_details';

    protected $fillable = [
        'product_id',
        'detail_id',
        'value_id'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function detail()
    {
        return $this->belongsTo(Detail::class, 'detail_id');
    }

    public function value()
    {
        return $this->belongsTo(Value::class, 'value_id');
    }

}
