<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attribute extends Model
{
    use HasFactory;

    protected $fillable = [
        'detail_id',
        'name'
    ];

    public function detail(){
        return $this->belongsTo(Detail::class);
    }

    public function values(){
        return $this->hasMany(Value::class);
    }
}
