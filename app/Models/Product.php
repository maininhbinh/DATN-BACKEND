<?php

namespace App\Models;

use Cviebrock\EloquentSluggable\Sluggable;
use Cviebrock\EloquentSluggable\SluggableScopeHelpers;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes, Sluggable, SluggableScopeHelpers;

    const TYPE_DISCOUNT = [
        'percentage',
        'fixed'
    ];

    protected $fillable = [
        'thumbnail',
        'name',
        'content',
        'category_id',
        'brand_id',
        'is_active',
        'is_hot_deal',
        'is_good_deal',
        'is_new',
        'is_show_home',
        'type_discount',
        'discount',
        'total_review',
        'avg_stars',
        'public_id',
        'slug'
    ];

    public function category(){
        return $this->belongsTo(Category::class);
    }

    public function brand(){
        return $this->belongsTo(Brand::class);
    }

    public function products(){
        return $this->hasMany(ProductItem::class);
    }

    public function galleries(){
        return $this->hasMany(Gallery::class);
    }

    public function details(){
        return $this->belongsToMany(Detail::class, 'product_details');
    }

    public function values(){
        return $this->belongsToMany(Value::class, 'product_values');
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
