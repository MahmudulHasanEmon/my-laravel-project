<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Otp extends Model
{
    protected $fillable = [
        'phone',
        'session_token',
        'otp_code',
        'verified',
        'revoked',
        'device_id',
        'ip',
        'user_agent',
        'attempts',
        'expires_at',
        'used_at',
    ];

    protected $casts = [
        'verified' => 'boolean',
        'revoked' => 'boolean',
        'attempts' => 'integer',
        'expires_at' => 'datetime',
        'used_at' => 'datetime',
    ];
    
}
