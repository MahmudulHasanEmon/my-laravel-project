<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Token;
use App\Models\User\User;
use App\Helpers\StatusCode;
use App\Helpers\ApiResponse;
use App\Helpers\User\JwtHelper;
use Illuminate\Support\Facades\Log;


class UserMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next)
    {
        try {
            $token = $request->bearerToken();
            if (!$token) {
                return ApiResponse::error(
                    StatusCode::UNAUTHORIZED,
                    'Token required'
                );
            }

            // Decode JWT
            try {
                $decoded = JwtHelper::decode($token);
            } catch (\Exception $e) {
                Log::warning('JWT decode failed: ' . $e->getMessage());
                return ApiResponse::error(
                    StatusCode::SESSION_EXPIRED,
                    'Invalid or expired token'
                );
            }

            $jti = $decoded->jti ?? null;
            if (!$jti) {
                return ApiResponse::error(
                    StatusCode::UNAUTHORIZED,
                    'Invalid token payload'
                );
            }

            $active = Token::where('jti', $jti)->first();
            if (!$active) {
                return ApiResponse::error(
                    StatusCode::UNAUTHORIZED,
                    'Token not found in server'
                );
            }

            if ($active->revoked) {
                return ApiResponse::error(
                    StatusCode::UNAUTHORIZED,
                    'Token has been revoked'
                );
            }

            if ($active->expires_at && $active->expires_at->lt(now())) {
                return ApiResponse::error(
                    StatusCode::SESSION_EXPIRED,
                    'Token has expired'
                );
            }

            // 6) Device Check
            $deviceId = $request->header('X-Device-Id') ?? $request->device_id;

            if ($active->device_id && $deviceId !== $active->device_id) {
                return ApiResponse::error(
                    StatusCode::UNAUTHORIZED,
                    'Device mismatch'
                );
            }

            $user = User::where('user_id', $active->phone)->with([
                'role',
                'permissions',
                'securityPreference',
                'tokens'
            ])->first();

            $securityPreference = $user->securityPreference;

            if (!$user || !$securityPreference) {
                return ApiResponse::error(
                    StatusCode::UNAUTHORIZED,
                    'User not found'
                );
            }

            // 🔹 New checks
            if ($securityPreference->locked_at) {

                return ApiResponse::error(
                    StatusCode::USER_BLOCKED,
                    'Account is already locked',
                    [
                        'locked_reason' => $securityPreference->locked_reason,
                        'locked_at' => $securityPreference->locked_at,
                    ]
                );
            }
            
            // Optional: Fingerprint check (deviceId + User-Agent)
            $fingerprint = hash('sha256', $deviceId . '|' . $request->header('User-Agent'));


            if ($active->fingerprint && $active->fingerprint !== $fingerprint) {
                return ApiResponse::error(
                    StatusCode::UNAUTHORIZED,
                    'Fingerprint mismatch'
                );
            }

            $securityPreference->last_activity_at = now();
            $securityPreference->save();

            // Assign user & token info to request
            $request->user = $user;
            $request->securityPreference = $securityPreference;
            $request->jwt_payload = $decoded;
            $request->active_token = $active;

            return $next($request);

        } catch (\Throwable $e) {
            Log::error('JwtMiddleware error: ' . $e->getMessage(), ['request' => $request->all()]);

            return ApiResponse::error(
                StatusCode::INTERNAL_SERVER_ERROR,
                'An error occurred while processing the request.',
                $e->getMessage()
            );
        }
    }
}
