<?php

namespace App\Models\User;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Token;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    //-------------------
    // Fillable
    //-------------------

    protected $fillable = [
        'user_id',
        'referral_code',
        'name',
        'father_name',
        'mother_name',
        'birthday',
        'gender',
        'marital_status',
        'nationality',
        'occupation',
        'email',
        'contact_phone',
        'income_source',
        'nid_number',
        'address',
        'password',
        'pin',
        'available_balance',
        'hold_balance',
        'stock_balance',
        'profile_url',
        'account_reference',
        'kyc_verified',
        'account_id',
        'kyc_document_url',
        'incoming_trans',
        'outgoing_trans'
    ];

    //-------------------
    // Casts
    //-------------------

    protected $casts = [
        'birthday' => 'date',
        'available_balance' => 'decimal:2',
        'hold_balance' => 'decimal:2',
        'stock_balance' => 'decimal:2',
        'kyc_document_url' => 'array',
        'incoming_trans' => 'boolean',
        'outgoing_trans' => 'boolean',
    ];

    // -------------------
    // User Security Preference Relation
    // -------------------

    public function securityPreference()
    {
        return $this->hasOne(UserSecurityPreference::class, 'user_id', 'user_id');
    }

    // -------------------
    // User Tokens Relation
    // -------------------

    public function tokens()
    {
        return $this->morphMany(Token::class, 'tokenable');
    }

    // -------------------
    // User Role Relation
    // -------------------

    public function role()
    {
        return $this->belongsTo(UserRole::class, 'account_id')->where('is_active', true);
    }

    // -------------------
    // User Role Permissions Relation
    // -------------------

    public function permissions()
    {
        return $this->belongsToMany(UserPermission::class, 'user_role_permission', 'id', 'permission_id')->where('user_permissions.is_active', true);
    }

    // -------------------
    // User Permission Overrides Relation
    // -------------------

    public function permissionOverrides()
    {
        return $this->hasMany(UserPermissionOverride::class);
    }

    // -------------------
    // Check if user has permission
    // -------------------

    public function hasPermission(string $permissionName): bool
    {
        // 1️⃣ Check override first
        $override = $this->permissionOverrides()
            ->whereHas('permission', function ($q) use ($permissionName) {
                $q->where('name', $permissionName)
                    ->where('is_active', true);
            })
            ->first();


        if ($override) {
            return $override->isActive();
        }

        // 2️⃣ Fallback to role permissions
        return $this->role?->permissions()
            ->where('name', $permissionName)
            ->where('is_active', true)
            ->exists();
    }

    // -------------------
    // Set user-specific permission override
    // -------------------

    public function setPermissionOverride(string $permissionName, bool $isActive = true)
    {
        $permission = UserPermission::where('name', $permissionName)->firstOrFail();

        return UserPermissionOverride::updateOrCreate(
            [
                'user_id' => $this->id,
                'permission_id' => $permission->id,
            ],
            [
                'is_active' => $isActive,
            ]
        );
    }

    // -------------------
    // Get all active permissions for the user
    // -------------------

    public function activePermissions()
    {
        // 1️⃣ Get role permissions (full rows, only active)
        $rolePermissions = $this->role
            ? $this->role
                ->permissions()
                ->where('is_active', true)
                ->get()
                ->keyBy('id')   // important for override replace
            : collect();

        // 2️⃣ Get user permission overrides
        $overrides = $this->permissionOverrides()
            ->with('permission')
            ->get();

        foreach ($overrides as $override) {

            if (!$override->permission) {
                continue;
            }

            $permission = $override->permission;

            // ❌ Override: disable permission
            if ($override->is_active === false) {
                $rolePermissions->forget($permission->id);
            }

            // ✅ Override: enable permission
            if ($override->is_active === true) {
                $rolePermissions->put($permission->id, $permission);
            }
        }

        // 3️⃣ Return clean collection
        return $rolePermissions->values();
    }


    // -------------------
    // Transaction Relations
    // -------------------


    public function transactionLimits()
    {
        // users.account_id -> transaction_limits.role_id
        return $this->hasMany(TransactionLimit::class, 'role_id', 'account_id');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'user_id', 'user_id')->orderBy('id', 'desc');
    }

    public function lastFiveTransactions()
    {
        return $this->transactions()->latest()->take(5);
    }

}
