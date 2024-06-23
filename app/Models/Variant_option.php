<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Variant_option extends Model
{
    use HasFactory;
    protected $fillable = [
        'variant_id',
        'name'
    ];
}
