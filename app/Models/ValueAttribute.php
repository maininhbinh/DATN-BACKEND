<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ValueAttribute extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $fillable = ['parameter_id', 'value'];
    public function parameter()
    {
        return $this->belongsTo(Parameter::class, 'parameter_id', 'id');
    }
}
