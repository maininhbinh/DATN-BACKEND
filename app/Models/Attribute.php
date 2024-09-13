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
        return $this->belongsToMany(Detail::class, 'detail_attributes');
    }

    public function values(){
        return $this->belongsToMany(Value::class, 'attribute_values');
    }

    public function category(){
        return $this->belongsToMany(Category::class, 'attribute_categories');
    }
}
