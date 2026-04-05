<?php

namespace App\Http\Middleware;

use App\Helpers\ApiResponse;
use App\Helpers\StatusCode;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAdminPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $permission)
    {
        $admin = $request->user();

        if (!$admin || !$admin->hasPermission($permission)) {
            return ApiResponse::error(
                StatusCode::METHOD_NOT_ALLOWED,
                StatusCode::message(StatusCode::METHOD_NOT_ALLOWED)
            );
        }
        
        return $next($request);
    }
}
