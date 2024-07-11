<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Variant extends Model
{
    use HasFactory,SoftDeletes;
    protected $fillable = [
       'category_id',
        'name'
    ];

   

    public function variants()
    {
        return $this->hasMany(Variant_option::class);
    }
    public function variantOptions()
    {
        return $this->hasMany(Variant_option::class);
    }
}
