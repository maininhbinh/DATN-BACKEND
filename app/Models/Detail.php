<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Detail extends Model
{
    use HasFactory;

    protected $table = 'details';

    protected $fillable = [
        'name'
    ];


    public function category(){
        return $this->belongsToMany(Category::class, 'detail_categories');
    }

    public function attributes(){
        return $this->belongsToMany(Attribute::class, 'detail_attributes');
    }

}
