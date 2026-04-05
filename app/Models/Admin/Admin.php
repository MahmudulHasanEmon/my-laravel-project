<?php

namespace App\Models\Admin;

use App\Models\Token;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Admin extends Model
{
    use HasFactory;

    protected $fillable = [
        'admin_id',
        'name',
        'password',
        'available_balance',
        'hold_balance',
        'stock_balance',
        'profile_url',
        'fcm_token',
        'app_version',
        'admin_status',
        'status_message',
        'device_id',
        'device_info',
        'ip_address',
        'last_login_at',
        'last_activity_at',
        'login_attempts',
        'is_locked',
        'locked_at',
        'password_changed_at',
        'two_factor_enabled',
        'two_factor_secret',
        'notification_enabled',
        'notification_category',
        'language_preference',
        'theme_mode',
        'nid_number',
        'verification_doc',
        'verified_at',
        'created_by',
        'updated_by',
        'remarks',
        'timezone',
    ];

    /**
     * Hidden fields (like password) when converting to array or JSON
     */
    protected $hidden = [
        'password',
        'two_factor_secret',
    ];

    protected $casts = [
        'available_balance' => 'decimal:2',
        'hold_balance' => 'decimal:2',
        'stock_balance' => 'decimal:2',
        'admin_status' => 'boolean',
        'is_locked' => 'boolean',
        'two_factor_enabled' => 'boolean',
        'notification_enabled' => 'boolean',
        'notification_category' => 'array', // JSON type cast
        'permissions' => 'array', // JSON type cast
        'last_login_at' => 'datetime',
        'last_activity_at' => 'datetime',
        'locked_at' => 'datetime',
        'password_changed_at' => 'datetime',
        'verified_at' => 'datetime',
    ];

    public function tokens()
    {
        return $this->morphMany(Token::class, 'tokenable');
    }

    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = bcrypt($value);
    }

    public function role()
    {
        return $this->belongsTo(AdminRole::class, 'role_id')->where('is_active', true);
    }

    public function hasPermission($permissionName)
    {
        return $this->role?->permissions()
            ->where('name', $permissionName)
            ->where('is_active', true)
            ->exists();
    }

    public function permissions()
    {
        return $this->belongsToMany(AdminPermission::class, 'admin_role_permission', 'role_id', 'permission_id')->where('admin_permissions.is_active', true);
    }
}
