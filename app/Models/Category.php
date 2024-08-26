<?php

namespace App\Models;

use Cviebrock\EloquentSluggable\Sluggable;
use Cviebrock\EloquentSluggable\SluggableScopeHelpers;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use HasFactory, SoftDeletes, Sluggable, SluggableScopeHelpers;

    protected $fillable = [
        'name',
        'image',
        'slug',
        'active',
    ];

    public function details(){
        return $this->belongsToMany(Detail::class, 'detail_categories');
    }

    public function attributes(){
        return $this->belongsToMany(Attribute::class, 'attribute_categories');
    }

    public function variants(){
        return $this->hasMany(Variant::class);
    }

    public function products(){
        return $this->hasMany(Product::class);
    }

    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'name'
            ]
        ];
    }
}
