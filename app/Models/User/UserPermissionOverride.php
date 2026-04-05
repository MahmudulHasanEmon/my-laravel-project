<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserPermissionOverride extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'permission_id',
        'is_active',
        'is_hidden',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_hidden' => 'boolean',
    ];

    // Relations
    public function user()
    {
        return $this->belongsTo(User::class);
    }


    public function permission()
    {
        return $this->belongsTo(UserPermission::class, 'permission_id');
    }
    
    // Helper to check if active
    public function isActive(): bool
    {
        return $this->is_active === true;
    }

    // Helper to check if hidden
    public function isHidden(): bool
    {
        return $this->is_hidden === true;
    }


}
