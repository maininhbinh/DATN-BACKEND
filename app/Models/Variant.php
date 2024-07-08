<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Variant extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'category_id'
    ];

    public function variants(){
        return $this->hasMany(Varian_option::class);
    }
}
