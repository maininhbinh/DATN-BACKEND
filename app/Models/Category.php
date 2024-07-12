<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'image',
        'parent_id',
        'public_id',
        'active',
    ];

    public function details(){
        return $this->belongsToMany(Detail::class, 'detail_categories');
    }

    public function parent(){
        return $this->belongsTo(Category::class);
    }

    public function variants(){
        return $this->hasMany(Variant::class);
    }
}
