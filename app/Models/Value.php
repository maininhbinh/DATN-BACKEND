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

    public function details(){
        return $this->hasMany(Product_detail::class, 'value_id');
    }
}
