<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserPermission extends Model
{
    use SoftDeletes;
    
    protected $fillable = ['name', 'display_name', 'module', 'show_in', 'description', 'is_active', 'is_hidden'];

    protected $casts = [
        'is_active' => 'boolean',
        'is_hidden' => 'boolean',
    ];


    
    public function roles()
    {
        return $this->belongsToMany(UserRole::class, 'user_role_permission', 'permission_id', 'role_id');
    }
}
