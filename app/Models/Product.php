<?php

namespace App\Models;

use Cviebrock\EloquentSluggable\Sluggable;
use Cviebrock\EloquentSluggable\SluggableScopeHelpers;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory, SoftDeletes, Sluggable, SluggableScopeHelpers;

    protected $table = 'products';

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

    public function comments(){
        return $this->hasMany(Comment::class);
    }

    public function getAverageRatingAttribute()
    {
        return $this->comments()->avg('rating');
    }

    public function orderDetails(){
        return $this->hasManyThrough(OrderDetail::class, ProductItem::class, 'product_id', 'product_item_id');
    }

}
