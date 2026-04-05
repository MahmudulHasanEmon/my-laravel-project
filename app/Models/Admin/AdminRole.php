<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;

class AdminRole extends Model
{
    protected $fillable = ['name', 'display_name', 'description', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];
    
    public function permissions()
    {
        return $this->belongsToMany(AdminPermission::class, 'admin_role_permission', 'role_id', 'permission_id')
            ->where('admin_permissions.is_active', true);
    }

    public function admins()
    {
        return $this->hasMany(Admin::class, 'role_id');
    }
}
