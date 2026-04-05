<?php

namespace App\Http\Middleware;

use App\Helpers\ApiResponse;
use App\Helpers\StatusCode;
use Log;
use Closure;
use App\Models\Token;
use App\Models\Admin\Admin;
use Illuminate\Http\Request;
use App\Helpers\Admin\JwtHelper;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            // 1) Get Bearer Token
            $tokenString = $request->bearerToken();
            if (!$tokenString) {

                return ApiResponse::error(
                    StatusCode::UNAUTHORIZED,
                    'Token required'
                );
            }

            // 2) Decode JWT
            try {
                $decoded = JwtHelper::decode($tokenString);
            } catch (\Exception $e) {
                Log::warning('JWT decode failed: ' . $e->getMessage());

                return ApiResponse::error(
                    StatusCode::SESSION_EXPIRED,
                    'Invalid or expired token'
                );
            }
            
            // 3) JTI Check
            $jti = $decoded->jti ?? null;
            if (!$jti) {
                return ApiResponse::error(
                    StatusCode::UNAUTHORIZED,
                    'Invalid token payload'
                );

            }


            // 4) Token lookup in DB
            $token = Token::where('jti', $jti)->first();
            if (!$token) {

                return ApiResponse::error(
                    StatusCode::UNAUTHORIZED,
                    'Token not found in server'
                );
            }

            // 5) Revoked / Expired check
            if ($token->revoked) {
                return ApiResponse::error(
                    StatusCode::UNAUTHORIZED,
                    'Token has been revoked'
                );
            }

            if ($token->expires_at && $token->expires_at->lt(now())) {
                return ApiResponse::error(
                    StatusCode::SESSION_EXPIRED,
                    'Token has expired'
                );
            }

            // 6) Device Check
            $deviceIdFromClient = $request->header('X-Device-Id') ?? $request->device_id;

            if ($token->device_id && $deviceIdFromClient !== $token->device_id) {
                return ApiResponse::error(
                    StatusCode::UNAUTHORIZED,
                    'Device mismatch'
                );
            }

            // 7) Admin Lookup
            $admin = Admin::where('admin_id', $token->phone)->first();
            if (!$admin) {
                return ApiResponse::error(
                    StatusCode::UNAUTHORIZED,
                    'Admin not found'
                );
            }

            // 8) Account Locked Check
            if ($admin->locked_at) {
                return ApiResponse::error(
                    StatusCode::USER_BLOCKED,
                    'Account is already locked',
                    [
                        'locked_reason' => $admin->locked_reason,
                        'locked_at' => $admin->locked_at,
                    ]
                );

            }

            // 9) Extra Device Validation
            if (!empty($admin->device_id) && $admin->device_id !== $deviceIdFromClient) {
                return ApiResponse::error(
                    StatusCode::UNAUTHORIZED,
                    'Admin device mismatch'
                );
            }

            // 10) Fingerprint Check
            $fingerprint = hash('sha256', $request->ip() . '|' . $request->header('User-Agent'));
            if ($token->fingerprint && $token->fingerprint !== $fingerprint) {
                return ApiResponse::error(
                    StatusCode::UNAUTHORIZED,
                    'Fingerprint mismatch'
                );
            }

            $admin->last_activity_at = now();
            $admin->save();

            // 11) Attach Admin & Token to Request
            $request->merge([
                'admin' => $admin->toArray(),
                'jwt_payload' => (array) $decoded,
                'active_token' => $token->toArray(),
            ]);
            
            // 11) Attach Admin Model to Request (so you can do $request->user() or $request->admin)
            $request->setUserResolver(fn() => $admin);

            // Optional: attach token payload if needed
            $request->attributes->set('active_token', $token);
            $request->attributes->set('jwt_payload', $decoded);

            return $next($request);

        } catch (\Throwable $e) {
            Log::error('AdminMiddleware error: ' . $e->getMessage(), [
                'request' => $request->all(),
            ]);

            return ApiResponse::error(
                StatusCode::INTERNAL_SERVER_ERROR,
                'An error occurred while processing the request.',
                $e->getMessage()
            );
        }
    }
}
