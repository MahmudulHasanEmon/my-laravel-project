<?php

namespace App\Http\Controllers\Admin;
use App\Helpers\ApiResponse;
use App\Helpers\StatusCode;
use DB;
use Log;
use App\Models\Otp;
use App\Models\Admin\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Helpers\Admin\JwtHelper;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AdminController extends Controller
{
    public function register(Request $request)
    {
        try {
            // ✅ 1. Validate Request
            $validated = $request->validate([
                'admin_id' => 'required|digits:13|unique:admins,admin_id',
                'name' => 'required|string|max:255',
                'password' => 'required|string|min:6',
                'profile_img' => 'required|image|mimes:jpg,jpeg,png|max:2048',
            ]);

            // ✅ 2. Handle Profile Image Upload
            $profilePath = null;
            if ($request->hasFile('profile_img')) {
                $profilePath = $request->file('profile_img')->store('admins', 'public');
            }


            // ✅ 3. Create Admin
            $admin = Admin::create([
                'admin_id' => $validated['admin_id'],
                'name' => $validated['name'],
                'password' => $validated['password'],
                'profile_url' => $profilePath ? asset('storage/' . $profilePath) : null,
                'created_by' => optional($request->user())->id, // safer access
            ]);

            // ✅ 4. Return Success
            return response()->json([
                'status' => 'success',
                'message' => 'Admin created successfully',
                'data' => $admin
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            // ❌ Validation Error

            return ApiResponse::error(
                StatusCode::UNPROCESSABLE_ENTITY,
                'Validation failed',
                $e->errors(),
            );

        } catch (\Illuminate\Database\QueryException $e) {
            // ❌ Database Error (duplicate, constraint, etc.)
            return ApiResponse::error(
                StatusCode::INTERNAL_SERVER_ERROR,
                'Database error occurred',
                ['error' => $e->getMessage()],
            );

        } catch (\Exception $e) {
            // ❌ General Error Catch-All
            return ApiResponse::error(
                StatusCode::INTERNAL_SERVER_ERROR,
                'Something went wrong while creating admin',
                ['error' => $e->getMessage()],
            );
        }
    }

    public function pinLogin(Request $request)
    {
        try {
            // ✅ Validation
            $validated = $request->validate([
                'phone' => [
                    'required',
                    'digits:13',
                    'exists:admins,admin_id', // check phone exists in admin_id column
                ],
                'password' => 'required|string|min:5',
                'session_token' => 'required|string',
                'device_id' => 'required|string',
            ]);

            $phone = $validated['phone'];
            $deviceId = $validated['device_id'];

            // ✅ Find admin
            $admin = Admin::where('admin_id', $phone)->first();

            // Check account locked
            if ($admin && $admin->locked_at) {
                return ApiResponse::error(
                    StatusCode::USER_BLOCKED,
                    'Account is locked due to too many failed PIN attempts.',
                );
            }

            // ✅ OTP session check
            $session = Otp::where('phone', $phone)
                ->where('session_token', $validated['session_token'])
                ->where('verified', true)
                ->where('expires_at', '>=', now())
                ->first();

            if (!$session) {

                return ApiResponse::error(
                    StatusCode::SESSION_EXPIRED,
                    'OTP session expired or invalid. Please verify OTP again.'
                );
            }

            // ✅ Password check
            if (!$admin || !Hash::check($validated['password'], $admin->password)) {
                if ($admin) {
                    $admin->login_attempts += 1;

                    if ($admin->login_attempts >= 5) {
                        $admin->locked_at = now();
                    }

                    $admin->save();
                }

                return ApiResponse::error(
                    StatusCode::INVALID_PIN,
                    'Invalid password',
                    ['remaining_attempts' => $admin ? max(0, 5 - $admin->login_attempts) : 5]
                );
            }

            // ✅ Reset attempts if successful
            $admin->login_attempts = 0;
            $admin->last_login_at = now();
            $admin->save();

            // ✅ Create JWT + store token in transaction
            try {
                $jwt = DB::transaction(function () use ($admin, $deviceId, $request) {
                    // Revoke old tokens for this device
                    $admin->tokens()
                        ->where('device_id', $deviceId)
                        ->update(['revoked' => true]);

                    $fingerprint = hash('sha256', $request->ip() . '|' . $request->header('User-Agent'));
                    $jwt = JwtHelper::create(['phone' => $admin->admin_id], 15, $deviceId);

                    $admin->tokens()->updateOrCreate(
                        ['phone' => $admin->admin_id, 'device_id' => $deviceId],
                        [
                            'jti' => $jwt['jti'],
                            'fingerprint' => $fingerprint,
                            'expires_at' => Carbon::createFromTimestamp($jwt['exp']),
                            'revoked' => false,
                        ]
                    );

                    return $jwt;
                });
            } catch (\Throwable $e) {
                Log::error('pinLogin DB error: ' . $e->getMessage(), ['phone' => $phone]);
                return ApiResponse::error(
                    StatusCode::INTERNAL_SERVER_ERROR,
                    'Failed to create session token. Try again later.',
                    $e->getMessage()
                );
            }

            // ✅ Mark OTP session as used
            $session->delete();

            $admin->device_id = $deviceId;
            $admin->save();

            $admin->load('role.permissions');

            return ApiResponse::success(
                StatusCode::OK,
                'Login successful',
                [
                    'token' => $jwt['token'],
                    'expires_at' => Carbon::createFromTimestamp($jwt['exp'])->toDateTimeString(),
                    'admin_data' => [
                        'admin' => $admin,
                        'rule' => $admin->role,
                    ]
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
                'phone' => $request->input('phone'),
                'session_token' => $request->input('session_token'),
            ]);

            return ApiResponse::error(
                StatusCode::INTERNAL_SERVER_ERROR,
                'Something went wrong. Please try again later.',
                $e->getMessage(),
            );

        }
    }

    public function refreshToken(Request $request)
    {
        try {
            // 1) Validation
            $request->validate([
                'phone' => [
                    'required',
                    'digits:13',
                    'exists:admins,admin_id', // check phone exists in admin_id column
                ],
                'password' => 'required|string|min:5',
                'device_id' => 'required|string',
            ]);

            $phone = $request->phone;
            $deviceId = $request->device_id;

            // 2) Find user
            $admin = Admin::where('admin_id', $phone)->first();
            if (!$admin) {
                return ApiResponse::error(
                    StatusCode::NOT_FOUND,
                    'Admin not found',
                );
            }

            // 3) Check account lock
            if ($admin->locked_at) {
                return ApiResponse::error(
                    StatusCode::USER_BLOCKED,
                    'Account is locked due to too many failed PIN attempts.',
                );

            }

            // 4) Validate PIN
            if (!Hash::check($request->password, $admin->password)) {
                $admin->login_attempts += 1;

                if ($admin->login_attempts >= 5) {
                    $admin->locked_at = now();
                    $admin->locked_reason = 'Too many failed PIN attempts';
                }

                $admin->save();

                return ApiResponse::error(
                    StatusCode::INVALID_PIN,
                    'Invalid password remaining attempts ' . max(0, 5 - $admin->login_attempts),
                    ['remaining_attempts' => max(0, 5 - $admin->login_attempts)]
                );

            }

            // 5) Reset PIN attempts on success
            $admin->login_attempts = 0;
            $admin->save();

            // 6) Check active token
            $active = $admin->tokens()
                ->where('device_id', $deviceId)
                ->first();

            if (!$active) {

                return ApiResponse::error(
                    StatusCode::UNAUTHORIZED,
                    'No active token found for this device. Please login again.'
                );
            }

            if ($active->revoked) {
                return ApiResponse::error(
                    StatusCode::UNAUTHORIZED,
                    'Token has been revoked. Please login again.'
                );

            }

            // // 7) Revoke previous tokens for device
            // $admin->tokens()
            //     ->where('device_id', $deviceId)
            //     ->update(['revoked' => true]);

            $fingerprint = hash('sha256', $request->ip() . '|' . $request->header('User-Agent'));
            $jwt = JwtHelper::create(['phone' => $admin->admin_id], 15, $deviceId);

            $admin->tokens()->updateOrCreate(
                ['phone' => $admin->admin_id, 'device_id' => $deviceId],
                [
                    'jti' => $jwt['jti'],
                    'fingerprint' => $fingerprint,
                    'expires_at' => Carbon::createFromTimestamp($jwt['exp']),
                    'revoked' => false,
                ]
            );

            $admin->load('role.permissions');

            return ApiResponse::success(
                StatusCode::OK,
                'Token refreshed successfully',
                [
                    'token' => $jwt['token'],
                    'expires_at' => Carbon::createFromTimestamp($jwt['exp'])->toDateTimeString(),
                    'admin_data' => [
                        'admin' => $admin,
                        'rule' => $admin->role,
                    ]
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
                $e->getMessage(),
            );
        }
    }

    public function adminStatus(Request $request)
    {
        try {
            // 1️⃣ Validation
            $request->validate([
                'phone' => ['required', 'digits:13'],
                'device_id' => ['required', 'string'],
            ]);

            $phone = $request->phone;
            $deviceId = $request->device_id;

            // 2️⃣ Find admin (single source of truth)
            $admin = Admin::where('admin_id', $phone)->first();

            if (!$admin) {
                return ApiResponse::error(
                    StatusCode::NOT_FOUND,
                    'Admin not found',
                );
            }

            // 3️⃣ Check active token for this device
            $token = $admin->tokens()
                ->where('device_id', $deviceId)
                ->first();

            if (!$token) {
                return ApiResponse::error(
                    StatusCode::UNAUTHORIZED,
                    'No active session found for this device. Please login again.',
                );
            }

            // 4️⃣ Optional: revoked check (only if you use it)
            if (isset($token->revoked) && $token->revoked) {
                return ApiResponse::error(
                    StatusCode::UNAUTHORIZED,
                    'Session has been revoked. Please login again.',
                );
            }

            // 5️⃣ Success
            return ApiResponse::success(
                200,
                'Admin is active',
                [
                    'admin_status' => 'active',
                    'admin_id' => $admin->admin_id,
                ]
            );

        } catch (ValidationException $e) {
            return ApiResponse::error(
                StatusCode::UNPROCESSABLE_ENTITY,
                StatusCode::message(StatusCode::UNPROCESSABLE_ENTITY),
                [
                    'errors' => $e->errors(),
                    'internal_code' => StatusCode::UNPROCESSABLE_ENTITY,
                ]
            );

        } catch (\Throwable $e) {
            Log::error('Admin Status Error', [
                'message' => $e->getMessage(),
                'request' => $request->all(),
            ]);

            return ApiResponse::error(
                StatusCode::INTERNAL_SERVER_ERROR,
                'Something went wrong. Please try again later.',
                $e->getMessage(),
            );
        }
    }

    public function profile(Request $request)
    {
        return $request;
    }

    public function logout(Request $request)
    {
        // Delete all tokens associated with this phone
        $deleted = $request->user()->tokens()->delete();

        if ($deleted) {
            return ApiResponse::success(
                StatusCode::OK,
                'Logged out successfully',
            );

        }

        // If no token was found/deleted
        return ApiResponse::error(
            StatusCode::NOT_FOUND,
            'No active session found to logout',
        );
    }


}
