<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserRegítration extends Model
{
    use HasFactory;

    protected $fillable = [
        'OTP',
        'username',
        'email',
        'password',
        'otp_expires_at',
    ];
}
