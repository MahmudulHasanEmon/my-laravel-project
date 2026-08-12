<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;

class UserRole extends Model
{
    protected $fillable = ['name', 'display_name', 'description', 'is_active', 'is_hidden', 'priority'];

    protected $casts = [
        'is_active' => 'boolean',
        'is_hidden' => 'boolean',
        'priority' => 'integer'
    ];

    public function permissions()
    {
        return $this->belongsToMany(UserPermission::class, 'user_role_permission', 'role_id', 'permission_id')
            ->where('user_permissions.is_active', true)->orderBy('user_permissions.priority', 'asc');
    }

    public function users()
    {
        return $this->hasMany(User::class, 'account_id');
    }


}
