<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Parameters extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $table = 'parameters';
    protected $fillable = ['name', 'description'];
}
