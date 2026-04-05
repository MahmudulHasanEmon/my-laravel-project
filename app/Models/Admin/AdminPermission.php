<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;

class AdminPermission extends Model
{
    protected $fillable = ['name', 'display_name', 'group', 'show_in', 'description', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];
    
    public function roles()
    {
        return $this->belongsToMany(AdminRole::class, 'admin_role_permission', 'permission_id', 'role_id');
    }
}
