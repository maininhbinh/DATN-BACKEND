<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Variant extends Model
{
    use HasFactory,SoftDeletes;
    protected $fillable = [
        'name',
        'category_id'
    ];

    public function variants(){
        return $this->hasMany(VariantOption::class);
    }

    public function category(){
        return $this->belongsTo(Category::class);
    }
}
