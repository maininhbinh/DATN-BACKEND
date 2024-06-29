<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Detail extends Model
{
    use HasFactory;
    protected $table = 'details';
    protected $fillable = [
        'category_id',
        'name'
    ];


    public function category(){
        return $this->belongsTo(Category::class);
    }

    public function attributes(){
        return $this->hasMany(Attribute::class);
    }

    public function products(){
        return $this->hasMany(Product::class, 'product_details')->withPivot('value_id')->withTimestamps();
    }
}
