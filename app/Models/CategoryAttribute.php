<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CategoryAttribute extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $fillable = ['category_id','parameter_id'];
    
    public function category()
    {
        return $this->belongsTo(Categories::class);
    }

    public function parameter()
    {
        return $this->belongsTo(Parameter::class);
    }
}
