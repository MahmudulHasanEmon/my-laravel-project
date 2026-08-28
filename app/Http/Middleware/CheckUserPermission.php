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

        // TEST: Middleware actually running? 
        \Log::info('CheckUserPermission Middleware Called', ['permission' => $permission, 'user' => $request->user(),]);
        $user = $request->user;

        if (!$user) {
            return ApiResponse::error(StatusCode::UNAUTHORIZED, StatusCode::message(StatusCode::UNAUTHORIZED));
        }

        // User is not authenticated or does not have permission
        if (!$user || !$user->hasPermission($permission)) {
            return ApiResponse::error(
                StatusCode::METHOD_NOT_ALLOWED,
                StatusCode::message(StatusCode::METHOD_NOT_ALLOWED)
            );
        }

        return $next($request);
    }
}
