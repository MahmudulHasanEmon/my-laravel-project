<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Helpers\StatusCode;
use App\Models\Otp;
use App\Models\User\User;
use App\Models\Admin\Admin;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class OtpController extends Controller
{
    /**
     * Send OTP to the admin's phone.
     */
    public function sendOtpAdmin(Request $request)
    {
        try {
            $request->validate([
                'phone' => [
                    'required',
                    'digits:13',
                    'exists:admins,admin_id'
                ],
                'device_id' => 'nullable|string'
            ]);

            $deviceId = $request->device_id ?? null;

            // Check if OTP was sent in last 2 minutes
            $lastOtp = Otp::where('phone', $request->phone)
                ->latest('created_at')
                ->first();

            if ($lastOtp) {
                $expiresAt = $lastOtp->created_at->addMinutes(2);

                if (now()->lessThan($expiresAt)) {
                    $secondsLeft = now()->diffInSeconds($expiresAt);

                    $minutes = intdiv($secondsLeft, 60);
                    $seconds = $secondsLeft % 60;

                    $wait = sprintf('%02d min %02d sec', $minutes, $seconds);

                    return ApiResponse::error(
                        StatusCode::TOO_MANY_REQUESTS,
                        "Please wait $wait before requesting a new OTP."
                    );
                }
            }

            $otp = rand(10000, 99999);
            $sessionToken = Str::uuid()->toString();

            Otp::updateOrCreate(
                [
                    'phone' => $request->phone,
                ],
                [
                    'session_token' => $sessionToken,
                    'otp_code' => Hash::make($otp),
                    'device_id' => $deviceId,
                    'ip' => $request->ip(),
                    'user_agent' => $request->header('User-Agent'),
                    'expires_at' => now()->addMinutes(5),
                    'used_at' => null,
                    'attempts' => 0,
                    'verified' => false,
                ]
            );

            // SmsService::send($request->phone, "Your OTP is $otp");
            return ApiResponse::success(
                StatusCode::OK,
                'OTP sent successfully',
                [
                    'otp_preview' => $otp, // Remove in production
                    'session_token' => $sessionToken,
                ]
            );

        } catch (ValidationException $e) {
            return ApiResponse::error(
                StatusCode::UNPROCESSABLE_ENTITY,
                'Validation error',
                $e->errors(),
            );

        } catch (\Exception $e) {
            Log::error('Send OTP Error: ' . $e->getMessage());
            return ApiResponse::error(
                StatusCode::INTERNAL_SERVER_ERROR,
                'Failed to send OTP. Please try again later.',
                $e->getMessage(),
            );
        }
    }

    /**
     * Verify the OTP entered by the admin.
     */
    public function verifyOtpAdmin(Request $request)
    {
        try {
            // 1️⃣ Validation
            $request->validate([
                'phone' => [
                    'required',
                    'digits:13',
                    'exists:admins,admin_id', // assuming your Admin phone column is 'phone'
                ],
                'session_token' => 'required|string',
                'otp' => 'required|digits:5',
            ]);

            // 2️⃣ Fetch OTP session
            $session = Otp::where('phone', $request->phone)
                ->where('session_token', $request->session_token)
                ->whereNull('used_at')
                ->where('expires_at', '>=', now())
                ->first();

            if (!$session) {
                return ApiResponse::error(
                    StatusCode::SESSION_EXPIRED,
                    'Invalid or expired OTP session.',
                );
            }

            // 3️⃣ Attempts limiting
            if ($session->attempts >= 5) {
                return ApiResponse::error(
                    StatusCode::TOO_MANY_REQUESTS,
                    'Too many attempts. Please request a new OTP.',
                );
            }

            $session->increment('attempts');

            // 4️⃣ OTP check 
            if (!Hash::check($request->otp, $session->otp_code)) {
                return ApiResponse::error(
                    StatusCode::OTP_INVALID,
                    'Wrong OTP.',
                );
            }

            // 5️⃣ OTP verified
            $session->verified = true;
            $session->used_at = now();

            // 6️⃣ Find admin
            $admin = Admin::where('admin_id', $request->phone)->first();

            if ($admin) {
                $session->expires_at = now()->addMinutes(2); // PIN login lifetime
                $status = 'existing_user';
                $message = 'Account exists, please enter PIN within 2 minutes.';
                // 7️⃣ Revoke any existing active tokens for this admin
                $admin->tokens()->delete();

            } else {
                $session->expires_at = now()->addMinutes(10); // Registration lifetime
                $status = 'new_user';
                $message = 'No account found, please create account within 10 minutes.';
            }

            $session->save();

            // 8️⃣ Return success response
            return ApiResponse::success(
                StatusCode::OK,
                'OTP verified successfully',
                [
                    'status' => $status,
                    'session_token' => $session->session_token,
                ]
            );

        } catch (ValidationException $e) {
            return ApiResponse::error(
                StatusCode::UNPROCESSABLE_ENTITY,
                'Validation error',
                $e->errors(),
            );

        } catch (\Exception $e) {
            Log::error('Verify OTP Error: ' . $e->getMessage());
            return ApiResponse::error(
                StatusCode::INTERNAL_SERVER_ERROR,
                'OTP verification failed. Please try again later.',
                $e->getMessage(),
            );
        }
    }
    
    /**
     * Send OTP to the user's phone.
     */
    public function sendOtpUser(Request $request)
    {
        try {
            $request->validate([
                'phone' => 'required|digits:13',
                'device_id' => 'nullable|string'
            ]);

            $deviceId = $request->device_id ?? null;

            // Check if OTP was sent in last 2 minutes
            $lastOtp = Otp::where('phone', $request->phone)
                ->orderByDesc('created_at')
                ->first();

            if ($lastOtp && $lastOtp->created_at->gt(now()->subMinutes(2))) {
                $remaining = $lastOtp->created_at->addMinutes(2)->diff(now());
                $wait = sprintf('%d min %d sec', $remaining->i, $remaining->s);

                return ApiResponse::error(
                    StatusCode::TOO_MANY_REQUESTS,
                    "Please wait $wait before requesting a new OTP."
                );
            }

            $otp = rand(100000, 999999);
            $sessionToken = Str::uuid()->toString();

            Otp::updateOrCreate(
                [
                    'phone' => $request->phone,
                ],
                [
                    'session_token' => $sessionToken,
                    'otp_code' => Hash::make($otp),
                    'device_id' => $deviceId,
                    'ip' => $request->ip(),
                    'user_agent' => $request->header('User-Agent'),
                    'expires_at' => now()->addMinutes(5),
                    'used_at' => null,
                    'attempts' => 0,
                    'verified' => false,
                ]
            );


            // SmsService::send($request->phone, "Your OTP is $otp");

            Log::info("OTP for {$request->phone}: $otp");

            return ApiResponse::success(
                StatusCode::OK,
                'OTP sent successfully',
                [
                    'otp_preview' => $otp, // Remove in production
                    'session_token' => $sessionToken,
                ]
            );

        } catch (ValidationException $e) {

            return ApiResponse::error(
                StatusCode::UNPROCESSABLE_ENTITY,
                'Validation error',
                $e->errors(),
            );

        } catch (\Exception $e) {
            Log::error('Send OTP Error: ' . $e->getMessage());

            return ApiResponse::error(
                StatusCode::INTERNAL_SERVER_ERROR,
                'Failed to send OTP. Please try again later.',
                $e->getMessage(),
            );
        }
    }

    /**
     * Verify the OTP entered by the user.
     */
    public function verifyOtpUser(Request $request)
    {
        try {
            // 1️⃣ Validation
            $request->validate([
                'phone' => 'required|digits:13',
                'session_token' => 'required|string',
                'otp' => 'required|digits:6',
            ]);

            // 2️⃣ Fetch OTP session
            $session = Otp::where('phone', $request->phone)
                ->where('session_token', $request->session_token)
                ->whereNull('used_at')
                ->where('expires_at', '>=', now())
                ->first();

            if (!$session) {
                return ApiResponse::error(
                    StatusCode::SESSION_EXPIRED,
                    'Invalid or expired OTP session.',
                );
            }

            // 3️⃣ Attempts limiting
            if ($session->attempts >= 5) {
                return ApiResponse::error(
                    StatusCode::TOO_MANY_REQUESTS,
                    'Too many attempts. Please request a new OTP.',
                );
            }

            $session->increment('attempts');

            // 4️⃣ OTP check
            if (!Hash::check($request->otp, $session->otp_code)) {

                return ApiResponse::error(
                    StatusCode::OTP_INVALID,
                    'Wrong OTP.',
                );

            }

            // 5️⃣ OTP verified
            $session->verified = true;

            $user = User::where('user_id', $request->phone)->first();

            // 6️⃣ Set OTP session expiration
            if ($user) {
                $session->expires_at = now()->addMinutes(2); // PIN login lifetime
                $status = 'existing_user';
                $message = 'Account exists, please enter PIN within 2 minutes.';

                // 7️⃣ Revoke any existing active tokens for this phone
                $user->tokens()->delete();

            } else {
                $session->expires_at = now()->addMinutes(10); // Registration lifetime
                $status = 'new_user';
                $message = 'No account found, please create account within 10 minutes.';
            }

            $session->save();

            // 8️⃣ Return success response

            return ApiResponse::success(
                StatusCode::OK,
                'OTP verified successfully',
                [
                    'status' => $status,
                    'session_token' => $session->session_token,
                ]
            );

        } catch (ValidationException $e) {

            return ApiResponse::error(
                StatusCode::UNPROCESSABLE_ENTITY,
                'Validation error',
                $e->errors(),
            );

        } catch (\Exception $e) {
            Log::error('Verify OTP Error: ' . $e->getMessage());

            return ApiResponse::error(
                StatusCode::INTERNAL_SERVER_ERROR,
                'OTP verification failed. Please try again later.',
                $e->getMessage(),
            );

        }
    }

}
