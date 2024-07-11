<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VariantOption extends Model
{
    use HasFactory,SoftDeletes;

    protected $table = 'variant_options';

    protected $fillable = [
        'variant_id',
        'name'
    ];

    public function variant(){
        return $this->belongsTo(Variant::class);
    }

    public function products(){
        return $this->belongsToMany(ProductItem::class, 'product_configurations');

    }
}
