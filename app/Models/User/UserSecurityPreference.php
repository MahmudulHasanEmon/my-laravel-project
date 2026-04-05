<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserSecurityPreference extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'login_attempts',
        'pin_attempts',
        'is_locked',
        'locked_at',
        'locked_reason',
        'two_factor_enabled_email',
        'two_factor_enabled_phone',
        'two_factor_secret',
        'device_id',
        'device_type',
        'device_name',
        'device_model',
        'os',
        'os_version',
        'app_version',
        'ip_address',
        'location',
        'is_trusted',
        'is_blacklisted',
        'timezone',
        'language_preference',
        'theme_mode',
    ];
    
    protected $casts = [
        'is_locked' => 'boolean',
        'two_factor_enabled_email' => 'boolean',
        'two_factor_enabled_phone' => 'boolean',
        'is_trusted' => 'boolean',
        'is_blacklisted' => 'boolean',
        'locked_at' => 'datetime',
        'last_failed_login' => 'datetime',
        'password_changed_at' => 'datetime',
        'pin_changed_at' => 'datetime',
        'last_activity_at' => 'datetime',
        'last_login_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
}
