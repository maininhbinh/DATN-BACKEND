<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class ProductItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'product_items';

    protected $fillable = [
        'product_id',
        'price',
        'price_sale',
        'quantity',
        'sku',
        'image',
        'public_id',
    ];

    protected static function boot(){
        parent::boot();

        static::creating(function ($product) {
            $product->sku = self::generateUniqueSKU($product->sku);
        });
    }

    public static function generateUniqueSKU($sku)
    {
        $prefix = strtoupper(preg_replace('/[^A-Z0-9-]/', '', $sku)) ?? 'SKU';
        $randomPart = strtoupper(Str::random(6)); // Creates a random 6-character string
        $sku = "{$prefix}-{$randomPart}";

        // Ensure the SKU is unique
        while (self::where('sku', $sku)->exists()) {
            $randomPart = strtoupper(Str::random(6));
            $sku = "{$prefix}-{$randomPart}";
        }

        return $sku;
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function variants(){
        return $this->belongsToMany(VariantOption::class, 'product_configurations')->withPivot('id');
    }

    public function carts()
    {
        return $this->hasMany(Cart::class, 'product_item_id');
    }

}
