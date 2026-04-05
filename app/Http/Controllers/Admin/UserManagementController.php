<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\ApiResponse;
use App\Helpers\StatusCode;
use App\Http\Controllers\Controller;
use App\Models\User\User;
use App\Models\User\UserPermission;
use App\Models\User\UserRole;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class UserManagementController extends Controller
{
    public function index(Request $request)
    {
        // --------------------------
        // 1️⃣ Request parameters
        // --------------------------
        $perPage = max((int) $request->query('per_page', 10), 1);
        $userId = $request->query('user_id');
        $search = $request->query('search');
        $status = $request->query('status');
        $from = $request->query('from');
        $to = $request->query('to');

        // --------------------------
        // 2️⃣ Sorting map (SAFE)
        // --------------------------
        $sortMap = [
            'created_at' => 'users.created_at',
            'name' => 'users.name',
            'email' => 'users.email',
            'available_balance' => 'users.available_balance',
            'stock_balance' => 'users.stock_balance',
            'user_id' => 'users.user_id',
            'user_status' => 'usp.user_status',
            'last_activity_at' => 'usp.last_activity_at',
            'account_display_name' => 'ur.display_name',
        ];
        
        $sortKey = $request->query('sort_by', 'created_at');
        $sortBy = $sortMap[$sortKey] ?? 'users.created_at';
        $sortDir = $request->query('sort_dir') === 'asc' ? 'asc' : 'desc';

        // --------------------------
        // 3️⃣ Base query
        // --------------------------
        $query = User::query()
            ->leftJoin('user_security_preferences as usp', 'users.user_id', '=', 'usp.user_id')
            ->leftJoin('user_roles as ur', 'users.account_id', '=', 'ur.id')
            ->select([
                    'users.id',
                    'users.user_id',
                    'users.name',
                    'users.email',
                    'users.available_balance',
                    'users.stock_balance',
                    'users.profile_url',
                    'users.kyc_verified',
                    'users.created_at',

                    'usp.user_status as is_active',
                    'usp.last_activity_at',

                    'ur.name as account_name',
                    'ur.display_name as account_display_name',
                ]);

        // --------------------------
        // 4️⃣ Filters
        // --------------------------
        if (!empty($userId)) {
            $query->where('users.user_id', $userId);
        }

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('users.name', 'like', "%{$search}%")
                    ->orWhere('users.email', 'like', "%{$search}%")
                    ->orWhere('users.user_id', 'like', "%{$search}%");
            });
        }

        if ($status !== null) {
            $query->where('usp.user_status', (int) $status);
        }

        if ($from && $to) {
            try {
                $query->whereBetween('users.created_at', [
                    Carbon::parse($from)->startOfDay(),
                    Carbon::parse($to)->endOfDay(),
                ]);
            } catch (\Exception $e) {
                // ignore invalid date
            }
        }

        // --------------------------
        // 5️⃣ Sorting (NUMERIC SAFE)
        // --------------------------
        if (in_array($sortKey, ['available_balance', 'stock_balance'], true)) {
            $query->orderByRaw("CAST($sortBy AS DECIMAL(15,2)) $sortDir");
        } else {
            $query->orderBy($sortBy, $sortDir);
        }

        // --------------------------
        // 6️⃣ Pagination
        // --------------------------
        $users = $query
            ->paginate($perPage)
            ->appends($request->query());

        // --------------------------
        // 7️⃣ Transform
        // --------------------------
        $users->getCollection()->transform(function ($user) {
            return [
                'id' => $user->id,
                'user_id' => $user->user_id,
                'name' => $user->name,
                'email' => $user->email,
                'available_balance' => (float) $user->available_balance,
                'stock_balance' => (float) $user->stock_balance,
                'profile_url' => $user->profile_url,
                'kyc_verified' => (bool) $user->kyc_verified,

                'account_name' => $user->account_name,
                'account_display_name' => $user->account_display_name,

                'is_active' => (bool) ($user->is_active ?? false),
                'created_at' => $user->created_at,
                'last_activity_at' => $user->last_activity_at,
            ];
        });

        // --------------------------
        // 8️⃣ Response
        // --------------------------
        return ApiResponse::success(
            StatusCode::OK,
            'Users retrieved successfully',
            [
                'pagination' => [
                    'current_page' => $users->currentPage(),
                    'per_page' => $users->perPage(),
                    'total' => $users->total(),
                    'last_page' => $users->lastPage(),
                    'next_page_url' => $users->nextPageUrl(),
                    'prev_page_url' => $users->previousPageUrl(),
                    'from' => $users->firstItem(),
                    'to' => $users->lastItem(),
                ],
                'users' => $users->items(),
            ]
        );
    }

    public function userRolesPermissions(Request $request)
    {

        $roles = UserRole::withCount(['users', 'permissions'])->get();
        $permissions = UserPermission::withCount('roles')
            ->get()
            ->groupBy('group');  // group by `group` field

        return ApiResponse::success(
            StatusCode::OK,
            'User roles retrieved successfully',
            [
                'roles' => $roles,
                'permissions' => $permissions,
            ]
        );


    }

}