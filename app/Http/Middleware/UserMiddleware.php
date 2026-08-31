<?php

namespace App\Http\Middleware;

use App\Helpers\ApiResponse;
use App\Helpers\StatusCode;
use App\Helpers\User\JwtHelper;
use App\Models\Token;
use App\Models\User\User;
use Closure;
use Illuminate\Support\Facades\Hash;
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
                    StatusCode::UNPROCESSABLE_ENTITY,
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

            // Get PIN from header or request body
            $pin = $request->pin ?? $request->header('X-User-Pin');

            // PIN missing
            if (empty($pin)) {
                return ApiResponse::error(
                    StatusCode::UNAUTHORIZED,
                    'PIN required'
                );
            }


            // Validate PIN
            if (!Hash::check($pin, $user->pin)) {


                $securityPreference->pin_attempts += 1;

                if ($securityPreference->pin_attempts >= 5) {
                    $securityPreference->locked_at = now();
                    $securityPreference->locked_reason = 'Too many failed PIN attempts';
                }

                $securityPreference->save();

                return ApiResponse::error(
                    StatusCode::INVALID_PIN,
                    'Invalid PIN ' . max(0, 5 - $securityPreference->pin_attempts),
                    [
                        'remaining_attempts' => max(0, 5 - $securityPreference->pin_attempts),
                    ]
                );

            }

            // Reset failed attempts + Update last activity
            $securityPreference->pin_attempts = 0;
            $securityPreference->last_activity_at = now();
            $securityPreference->save();

            $request->setUserResolver(fn() => $user);

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
