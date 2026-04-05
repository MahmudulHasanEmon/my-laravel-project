<?php

namespace App\Http\Controllers\User;

use App\Helpers\ApiResponse;
use App\Helpers\StatusCode;
use App\Models\User\UserRole;
use DB;
use Carbon\Carbon;
use App\Models\Otp;
use App\Models\User\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Helpers\User\JwtHelper;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Models\User\UserSecurityPreference;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    /**
     * Register new user with PIN (after OTP verification)
     */

    public function registerWithPin(Request $request)
    {

        try {
            $request->validate([
                'phone' => 'required|digits:13|unique:users,user_id',
                'session_token' => 'required|string',
                'device_id' => 'required|string',
                'pin' => 'required|digits:5',
                'name' => 'required|string|max:100',
                'profile_img' => 'required|image|mimes:jpg,jpeg,png|max:2048',
            ]);
        } catch (ValidationException $e) {
            return ApiResponse::error(
                StatusCode::UNPROCESSABLE_ENTITY,
                'Validation failed' . $e->getMessage(),
                $e->errors(),
            );
        }

        $phone = $request->phone;
        $deviceId = $request->device_id;

        try {
            // ✅ 1. Verify OTP Session
            $session = Otp::where('phone', $phone)
                ->where('session_token', $request->session_token)
                ->where('verified', true)
                ->whereNull('used_at')
                ->where('expires_at', '>=', now())
                ->first();

            if (!$session) {
                return ApiResponse::error(StatusCode::SESSION_EXPIRED, 'OTP session expired or invalid. Please verify OTP again.');
            }

            DB::beginTransaction();

            // ✅ 2. Handle Profile Image Upload
            $profilePath = $request->file('profile_img')->store('users', 'public');
            $profileUrl = asset('storage/' . $profilePath);



            // ✅ 3. Create User
            $user = User::create([
                'user_id' => $phone,
                'pin' => Hash::make($request->pin),
                'name' => $request->name,
                'profile_url' => $profileUrl,
                'available_balance' => 0.00,
                'hold_balance' => 0.00,
                'stock_balance' => 0.00,
                'account_id' => UserRole::where('name', 'personal')->first()->id ?? null,
            ]);

            // ✅ 4. Create Security Preferences
            UserSecurityPreference::create([
                'user_id' => $user->user_id,
                'login_attempts' => 0,
                'pin_attempts' => 0,
                'is_locked' => false,
                'locked_at' => null,
                'locked_reason' => null,
                'last_failed_login' => null,
                'password_changed_at' => null,
                'pin_changed_at' => null,
                'two_factor_enabled_email' => false,
                'two_factor_enabled_phone' => false,
                'two_factor_secret' => null,
                'user_status' => true,
                'status_message' => null,
                'referral_code' => strtoupper(Str::random(8)),
                'device_id' => $deviceId,
                'device_type' => $request->header('User-Agent') ?? 'Unknown',
                'device_name' => $request->input('device_name', 'User Device'),
                'device_model' => $request->input('device_model', 'Unknown Model'),
                'os' => $request->input('os', 'Android'),
                'os_version' => $request->input('os_version', '1.0'),
                'app_version' => $request->input('app_version', '1.0.0'),
                'device_fcm_token' => $request->input('device_fcm_token', Str::random(32)),
                'ip_address' => $request->ip(),
                'location' => $request->input('location', 'Dhaka'),
                'last_login_at' => now(),
                'last_activity_at' => now(),
                'is_trusted' => false,
                'is_blacklisted' => false,
                'remarks' => null,
                'timezone' => 'Asia/Dhaka',
                'notification_enabled' => true,
                'language_preference' => 'en',
                'theme_mode' => 'light',
            ]);



            // ✅ 5. Generate JWT Token
            $jwt = JwtHelper::create(['phone' => $user->user_id], 15, $deviceId);

            $fingerprint = hash('sha256', $deviceId . '|' . $request->header('User-Agent'));

            // ✅ 6. Store Token
            $user->tokens()->Create(
                [
                    'phone' => $user->user_id,
                    'device_id' => $deviceId,
                    'jti' => $jwt['jti'],
                    'fingerprint' => $fingerprint,
                    'expires_at' => Carbon::createFromTimestamp($jwt['exp']),
                    'revoked' => false,
                ]
            );

            DB::commit();

            // ✅ Mark OTP Session Used
            $session->delete();
            $user->load('role.permissions');

            // ✅ Return Success Response
            return ApiResponse::success(
                StatusCode::CREATED,
                'User registered successfully',
                [
                    'token' => $jwt['token'],
                    'expires_at' => Carbon::createFromTimestamp($jwt['exp'])->toDateTimeString(),
                    'user_data' => [
                        'user' => $user,
                        'role' => $user->role,
                        'transactionLimits' => $user->transactionLimits,
                    ],
                ]
            );

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('User Registration Error: ' . $e->getMessage(), [
                'phone' => $phone,
                'session_token' => $request->session_token,
            ]);

            return ApiResponse::error(
                StatusCode::INTERNAL_SERVER_ERROR,
                'User registration failed. Please try again later.',
            );
        }

    }

    public function pinLogin(Request $request)
    {
        try {

            // 🔹 1. Validate inputs
            $validated = $request->validate([
                'phone' => 'required|digits:13',
                'pin' => 'required|digits:5',
                'session_token' => 'required|string',
                'device_id' => 'required|string',
            ]);

            $phone = $validated['phone'];
            $deviceId = $validated['device_id'];

            // 🔹 2. Find user
            $user = User::where('user_id', $phone)->first();

            if (!$user) {
                return ApiResponse::error(StatusCode::NOT_FOUND, 'User not found');
            }


            $securityPreference = $user->securityPreference;


            if (!$securityPreference) {
                return ApiResponse::error(StatusCode::INTERNAL_SERVER_ERROR, 'Security preferences not found for user');
            }

            // 🔹 3. Check if account locked
            if ($user && $securityPreference->locked_at) {
                return ApiResponse::error(StatusCode::USER_BLOCKED, 'Account is locked due to too many failed PIN attempts.', [
                    'locked_reason' => $securityPreference->locked_reason,
                    'locked_at' => $securityPreference->locked_at,
                ]);

            }

            // 🔹 4. Verify OTP session
            $session = Otp::where('phone', $phone)
                ->where('session_token', $request->session_token)
                ->where('verified', true)
                ->whereNull('used_at')
                ->where('expires_at', '>=', now())
                ->first();

            if (!$session) {
                return ApiResponse::error(StatusCode::SESSION_EXPIRED, 'OTP session expired or invalid. Please verify OTP again.');
            }

            // 4) Validate PIN
            if (!Hash::check($request->pin, $user->pin)) {
                $securityPreference->pin_attempts += 1;

                if ($securityPreference->pin_attempts >= 5) {
                    $securityPreference->locked_at = now();
                    $securityPreference->is_locked = true;
                    $securityPreference->locked_reason = 'Too many failed PIN attempts';
                }

                $securityPreference->save();

                return ApiResponse::error(StatusCode::INVALID_PIN, 'Invalid PIN', [
                    'remaining_attempts' => max(0, 5 - $securityPreference->pin_attempts)
                ]);
            }

            // 🔹 6. Reset attempts if PIN correct
            $securityPreference->pin_attempts = 0;
            $securityPreference->last_login_at = now();
            $securityPreference->save();

            // 🔹 7. Generate JWT + Token
            DB::beginTransaction();

            try {
                $fingerprint = hash('sha256', $deviceId . '|' . $request->header('User-Agent'));

                $jwt = JwtHelper::create(['phone' => $user->user_id], 15, $deviceId);


                $user->tokens()->updateOrCreate(
                    [
                        'phone' => $user->user_id,
                    ],
                    [
                        'device_id' => $deviceId,
                        'jti' => $jwt['jti'],
                        'fingerprint' => $fingerprint,
                        'expires_at' => Carbon::createFromTimestamp($jwt['exp']),
                        'revoked' => false,
                    ]
                );

                DB::commit();
            } catch (\Throwable $e) {
                DB::rollBack();
                Log::error('pinLogin DB error: ' . $e->getMessage(), ['phone' => $phone]);
                return ApiResponse::error(StatusCode::INTERNAL_SERVER_ERROR, 'Failed to create session token. Try again later.');
            }

            // 🔹 8. Consume OTP session
            $session->delete();
            $user->load('role.permissions');

            // 🔹 9. Success response
            return ApiResponse::success(
                StatusCode::OK,
                'PIN login successful',
                [
                    'token' => $jwt['token'],
                    'expires_at' => Carbon::createFromTimestamp($jwt['exp'])->toDateTimeString(),
                    'user_data' => [
                        'user' => $user,
                        'role' => $user->role,
                        'lastFiveTransactions' => $user->lastFiveTransactions,
                        'transactionLimits' => $user->transactionLimits,
                    ],
                ]
            );


        } catch (ValidationException $e) {
            return ApiResponse::error(
                StatusCode::UNPROCESSABLE_ENTITY,
                'Validation failed',
                $e->errors(),
            );
        } catch (\Throwable $e) {
            Log::error('PIN Login Error: ' . $e->getMessage(), [
                'phone' => $request->phone ?? null,
                'session_token' => $request->session_token ?? null,
            ]);

            return ApiResponse::error(
                StatusCode::INTERNAL_SERVER_ERROR,
                'Something went wrong. Please try again later.',
            );
        }
    }

    /**
     * Refresh token by providing phone + PIN (only if OTP session is still valid)
     * This allows user to get a new access token without requesting OTP again,
     * as long as OTP session window hasn't expired.
     */
    public function refreshToken(Request $request)
    {
        try {
            // 1) Validation
            $request->validate([
                'phone' => 'required|digits:13',
                'pin' => 'required|digits:5',
                'device_id' => 'required|string',
            ]);

            $phone = $request->phone;
            $deviceId = $request->device_id;

            // 2) Find user 
            $user = User::where('user_id', $phone)->first();

            if (!$user) {
                return ApiResponse::error(StatusCode::NOT_FOUND, 'User not found');
            }

            $securityPreference = $user->securityPreference;

            if (!$securityPreference) {
                return ApiResponse::error(StatusCode::INTERNAL_SERVER_ERROR, 'Security preferences not found for user');
            }

            // 3) Check account lock
            if ($securityPreference->locked_at) {
                return ApiResponse::error(StatusCode::FORBIDDEN, 'Account is locked due to too many failed PIN attempts.', [
                    'locked_reason' => $securityPreference->locked_reason,
                    'locked_at' => $securityPreference->locked_at,
                ]);

            }

            // 4) Validate PIN
            if (!Hash::check($request->pin, $user->pin)) {
                $securityPreference->pin_attempts += 1;

                if ($securityPreference->pin_attempts >= 5) {
                    $securityPreference->locked_at = now();
                    $securityPreference->locked_reason = 'Too many failed PIN attempts';
                }

                $securityPreference->save();

                return ApiResponse::error(StatusCode::INVALID_PIN, 'Invalid PIN with remaining attempts ' . max(0, 5 - $securityPreference->pin_attempts), [
                    'remaining_attempts' => max(0, 5 - $securityPreference->pin_attempts)
                ]);
            }


            // 5) Reset PIN attempts on success
            $securityPreference->pin_attempts = 0;

            $securityPreference->save();
            $user->save();

            // 6) Check active token
            $active = $user->tokens()
                ->where('device_id', $deviceId)
                ->first();

            if (!$active) {
                return ApiResponse::error(StatusCode::UNAUTHORIZED, 'No active token found for this device. Please login again.');
            }

            if ($active->revoked) {
                return ApiResponse::error(StatusCode::UNAUTHORIZED, 'Token has been revoked. Please login again.');
            }

            // 8) Issue new JWT
            $fingerprint = hash('sha256', $deviceId . '|' . $request->header('User-Agent'));

            $jwt = JwtHelper::create(['phone' => $user->user_id], 15, $deviceId);

            $user->tokens()->updateOrCreate(
                [
                    'phone' => $user->user_id,
                ],
                [
                    'device_id' => $deviceId,
                    'jti' => $jwt['jti'],
                    'fingerprint' => $fingerprint,
                    'expires_at' => Carbon::createFromTimestamp($jwt['exp']),
                    'revoked' => false,
                ]
            );

            $user->load('role.permissions');

            return ApiResponse::success(
                StatusCode::OK,
                'Token refreshed successfully',
                [
                    'token' => $jwt['token'],
                    'expires_at' => Carbon::createFromTimestamp($jwt['exp'])->toDateTimeString(),
                    'user_data' => [
                        'user' => $user,
                        'role' => $user->role,
                        'lastFiveTransactions' => $user->lastFiveTransactions,
                        'transactionLimits' => $user->transactionLimits,
                    ],
                ]
            );

        } catch (\Illuminate\Validation\ValidationException $e) {
            // Validation errors
            return ApiResponse::error(
                StatusCode::UNPROCESSABLE_ENTITY,
                'Validation failed',
                $e->errors(),
            );

        } catch (\Throwable $e) {
            // Log all other errors
            Log::error('Refresh Token Error: ' . $e->getMessage(), [
                'request' => $request->all(),
            ]);

            return ApiResponse::error(
                StatusCode::INTERNAL_SERVER_ERROR,
                'Something went wrong. Please try again later.',
            );
        }
    }

    /**
     * Logout - revoke current active token (and optionally revoke all device tokens)
     */

    public function logout(Request $request)
    {
        try {
            $user = $request->user;
            $activeToken = $request->active_token;

            if (!$user || !$activeToken) {
                return ApiResponse::error(
                    StatusCode::UNAUTHORIZED,
                    'Unauthorized user or invalid token.'
                );
            }

            $activeToken->delete();

            return ApiResponse::success(
                StatusCode::OK,
                'Logged out successfully'
            );

        } catch (\Throwable $e) {
            Log::error('Logout Error: ' . $e->getMessage());

            return ApiResponse::error(
                StatusCode::INTERNAL_SERVER_ERROR,
                'Logout failed. Please try again later.'
            );
        }
    }

    public function getBalance(Request $request)
    {
        try {
            $user = $request->user;

            if (!$user) {
                return ApiResponse::error(
                    StatusCode::UNAUTHORIZED,
                    'Unauthorized user.'
                );
            }

            return ApiResponse::success(
                StatusCode::OK,
                'Balance retrieved successfully',
                [
                    'available_balance' => $user->available_balance,
                    'hold_balance' => $user->hold_balance,
                    'stock_balance' => $user->stock_balance,
                ]
            );

        } catch (\Throwable $e) {
            Log::error('Get Balance Error: ' . $e->getMessage(), [
                'user_id' => $request->user->user_id ?? null,
            ]);

            return ApiResponse::error(
                StatusCode::INTERNAL_SERVER_ERROR,
                'Failed to retrieve balance. Please try again later.'
            );
        }
    }

    public function profile(Request $request)
    {
        return $request;
    }

    public function test(Request $request)
    {
        // Example: Disable a specific permission for a user
        $user = User::find(2);

        // Disable permission "user_transaction_create"
        $user->setPermissionOverride('user_transaction_create', false);
        // Enable permission "user_transaction_view"

        // Check permission
        // if ($user->hasPermission('user_transaction_create')) {
        //     return response()->json(["User can create transaction"]);
        // } else {
        //     return response()->json(["Permission denied"]);
        // }

        // Enable permission
        $permissions = $user->permissions();

        return response()->json([
            'message' => 'Permission override test completed.',
            'user_id' => $user->user_id,
            'active_permissions' => $permissions,
        ]);


    }


}
