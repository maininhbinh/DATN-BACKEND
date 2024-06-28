<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attribute extends Model
{
    use HasFactory;

    protected $fillable = [
        'detail_id',
        'name'
    ];

    public function details(){
        return $this->hasMany(Product_detail::class, 'detail_id');
    }
}
