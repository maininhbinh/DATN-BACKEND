<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Detail_category extends Model
{
    use HasFactory;

    protected $table = 'detail_categories';
    protected $fillable = [
        'detail_id',
        'category_id',
    ];
}