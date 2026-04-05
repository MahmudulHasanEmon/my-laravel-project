<?php

namespace Database\Seeders\Admin;

use App\Models\Admin\Admin;
use App\Models\Admin\AdminRole;
use Illuminate\Database\Seeder;
use App\Models\Admin\AdminPermission;


class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $role = AdminRole::create([
            'name' => 'owner',
            'display_name' => 'Owner',
            'description' => 'Has all access',
        ]);

        $permissions = [
            // 👤 Admin Management
            [
                'name' => 'admin',
                'display_name' => 'Admin',
                'group' => 'Admin Management',
                'show_in' => 'dashboard',
                'description' => 'Allows creating new admin users with specific roles.',
                'is_active' => true,
            ],
            [
                'name' => 'create_admin',
                'display_name' => 'Create Admin',
                'group' => 'Admin Management',
                'show_in' => 'sidebar',
                'description' => 'Allows creating new admin users with specific roles.',
                'is_active' => true,
            ],
            [
                'name' => 'send_money',
                'display_name' => 'Send Money',
                'group' => 'Admin Management',
                'show_in' => 'dashboard',
                'description' => 'Allows sending money to users.',
                'is_active' => true,
            ],
            [
                'name' => 'view_admins',
                'display_name' => 'View Admins',
                'group' => 'Admin Management',
                'show_in' => 'sidebar',
                'description' => 'Allows viewing the list and details of all admin accounts.',
                'is_active' => true,
            ],
            [
                'name' => 'edit_admins',
                'display_name' => 'Edit Admins',
                'group' => 'Admin Management',
                'show_in' => 'sidebar',
                'description' => 'Allows modifying admin profiles, roles, and status.',
                'is_active' => true,
            ],
            [
                'name' => 'delete_admins',
                'display_name' => 'Delete Admins',
                'group' => 'Admin Management',
                'show_in' => 'sidebar',
                'description' => 'Allows permanently removing admin users from the system.',
                'is_active' => true,
            ],

            // 🧠 Admin Role Management
            [
                'name' => 'create_admin_role',
                'display_name' => 'Create Admin Role',
                'group' => 'Admin Role Management',
                'show_in' => 'sidebar',
                'description' => 'Allows creating new roles for admin users.',
                'is_active' => true,
            ],
            [
                'name' => 'view_admin_roles',
                'display_name' => 'View Admin Roles',
                'group' => 'Admin Role Management',
                'show_in' => 'sidebar',
                'description' => 'Allows viewing all admin roles and their details.',
                'is_active' => true,
            ],
            [
                'name' => 'edit_admin_roles',
                'display_name' => 'Edit Admin Roles',
                'group' => 'Admin Role Management',
                'show_in' => 'sidebar',
                'description' => 'Allows editing role names, descriptions, or assigned permissions.',
                'is_active' => true,
            ],
            [
                'name' => 'delete_admin_roles',
                'display_name' => 'Delete Admin Roles',
                'group' => 'Admin Role Management',
                'show_in' => 'sidebar',
                'description' => 'Allows deleting existing admin roles from the system.',
                'is_active' => true,
            ],

            // 🔐 Admin Permission Management
            [
                'name' => 'create_admin_permission',
                'display_name' => 'Create Admin Permission',
                'group' => 'Admin Permission Management',
                'show_in' => 'sidebar',
                'description' => 'Allows creating new permissions that can be assigned to roles.',
                'is_active' => true,
            ],
            [
                'name' => 'view_admin_permissions',
                'display_name' => 'View Admin Permissions',
                'group' => 'Admin Permission Management',
                'show_in' => 'sidebar',
                'description' => 'Allows viewing the list and details of all system permissions.',
                'is_active' => true,
            ],
            [
                'name' => 'edit_admin_permissions',
                'display_name' => 'Edit Admin Permissions',
                'group' => 'Admin Permission Management',
                'show_in' => 'sidebar',
                'description' => 'Allows updating permission names, groups, and descriptions.',
                'is_active' => true,
            ],
            [
                'name' => 'delete_admin_permissions',
                'display_name' => 'Delete Admin Permissions',
                'group' => 'Admin Permission Management',
                'show_in' => 'sidebar',
                'description' => 'Allows deleting existing permissions from the system.',
                'is_active' => true,
            ],
            // 🧠 User Account Management
            [
                'name' => 'user',
                'display_name' => 'User',
                'group' => 'User Account Management',
                'show_in' => 'dashboard',
                'description' => 'Allows creation of new user accounts.',
                'is_active' => true,
            ],
            [
                'name' => 'create_user',
                'display_name' => 'Create User',
                'group' => 'User Account Management',
                'show_in' => 'sidebar',
                'description' => 'Allows creation of new user accounts.',
                'is_active' => true,
            ],
            [
                'name' => 'view_users',
                'display_name' => 'View Users',
                'group' => 'User Account Management',
                'show_in' => 'sidebar',
                'description' => 'Allows viewing the list and details of all users.',
                'is_active' => true,
            ],
            [
                'name' => 'edit_users',
                'display_name' => 'Edit Users',
                'group' => 'User Account Management',
                'show_in' => 'sidebar',
                'description' => 'Allows modification of user information and status.',
                'is_active' => true,
            ],
            [
                'name' => 'delete_users',
                'display_name' => 'Delete Users',
                'group' => 'User Account Management',
                'show_in' => 'sidebar',
                'description' => 'Allows permanent deletion of user accounts.',
                'is_active' => true,
            ],
            [
                'name' => 'notify_users',
                'display_name' => 'Notify Users',
                'group' => 'User Account Management',
                'show_in' => 'sidebar',
                'description' => 'Allows sending notifications to users.',
                'is_active' => true,
            ],
            [
                'name' => 'user_profile_view',
                'display_name' => 'View User Profile',
                'group' => 'User Account Management',
                'show_in' => 'sidebar',
                'description' => 'Allows viewing user profile information.',
                'is_active' => true,
            ],
            [
                'name' => 'user_role_permission_view',
                'display_name' => 'View User Permissions',
                'group' => 'User Account Management',
                'show_in' => 'sidebar',
                'description' => 'Allows viewing user permission information.',
                'is_active' => true,
            ],

            // 💳 User Transaction Management
            [
                'name' => 'user_transaction_create',
                'display_name' => 'Create User Transaction',
                'group' => 'User Transaction Management',
                'show_in' => 'sidebar',
                'description' => 'Allows creation of financial transactions related to users.',
                'is_active' => true,
            ],
            [
                'name' => 'user_transaction_view',
                'display_name' => 'View User Transactions',
                'group' => 'User Transaction Management',
                'show_in' => 'sidebar',
                'description' => 'Allows viewing transaction records and histories of users.',
                'is_active' => true,
            ],
            [
                'name' => 'user_transaction_edit',
                'display_name' => 'Edit User Transaction',
                'group' => 'User Transaction Management',
                'show_in' => 'sidebar',
                'description' => 'Allows editing or updating transaction details.',
                'is_active' => true,
            ],
            [
                'name' => 'user_transaction_delete',
                'display_name' => 'Delete User Transaction',
                'group' => 'User Transaction Management',
                'show_in' => 'sidebar',
                'description' => 'Allows removal or cancellation of a user transaction.',
                'is_active' => true,
            ],
        ];

        foreach ($permissions as $perm) {
            $p = AdminPermission::create($perm);
            $role->permissions()->attach($p->id);
        }

        Admin::factory()->create([
            'admin_id' => '8801775185654',
            'name' => 'MD. Mahmudul Hasan Emon',
            'password' => '87113',
            'device_id' => 'C74GPCV7AG',
            'role_id' => $role->id,
        ]);

        Admin::factory()->create([
            'admin_id' => '8801845416702',
            'name' => 'MD. Mahmudul Hasan Emon',
            'password' => '87113',
            'device_id' => 'C74GPCV7AG',
            'role_id' => $role->id,
        ]);

    }
}
