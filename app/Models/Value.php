<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Value extends Model
{
    use HasFactory;

    protected $fillable = [
        'attribute_id',
        'name',
    ];

    public function attributes(){
        return $this->belongsToMany(Attribute::class, 'attribute_values');
    }

    public function products(){
        return $this->belongsToMany(Product::class, 'product_values');
    }
}
