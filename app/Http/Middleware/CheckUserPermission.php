<?php

namespace App\Http\Middleware;

use App\Helpers\ApiResponse;
use App\Helpers\StatusCode;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckUserPermission
{
    /**
     * Handle an incoming request.
     */
    public function handle(
        Request $request,
        Closure $next,
        string $permission
    ): Response {

        // FIX: Call the method user() with parentheses
        $user = $request->user();

        // 1. Unauthenticated User Check
        if (!$user) {
            return ApiResponse::error(
                StatusCode::UNAUTHORIZED,
                StatusCode::message(StatusCode::UNAUTHORIZED)
            );
        }

        // 2. Permission Check (Recommended: Use FORBIDDEN / 403 instead of METHOD_NOT_ALLOWED / 405)
        if (!$user->hasPermission($permission)) {
            return ApiResponse::error(
                StatusCode::METHOD_NOT_ALLOWED,
                StatusCode::message(StatusCode::METHOD_NOT_ALLOWED)
            );
        }

        return $next($request);
    }
}