<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Categories extends Model
{
    use HasFactory;
    protected $tabel = 'categories';
    protected $fillable = ['name', 'description', 'parent_id'];
    public function parent()
    {
        return $this->belongsTo(Categories::class, 'parent_id');
    }

    // Quan hệ con
    public function children()
    {
        return $this->hasMany(Categories::class, 'parent_id');
    }
}
