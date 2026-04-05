<?php

namespace App\Http\Middleware;

use App\Helpers\ApiResponse;
use App\Models\User\TransactionLimit;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UserCheckTransactionLimit
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $trxType)
    {
        $user = $request->user;

        if (!$user) {
            return ApiResponse::error(
                Response::HTTP_UNAUTHORIZED,
                'Unauthorized'
            );
        }

        $amount = $request->amount;

        // 🔹 Get user's role
        $roleId = $user->account_id; // Assuming account_id is used as role_id for limits

        // 🔹 Get limit config
        $limit = TransactionLimit::where('role_id', $roleId)
            ->where('trx_type', $trxType)
            ->first();

        if (!$limit) {
            return ApiResponse::error(
                Response::HTTP_FORBIDDEN,
                'Transaction limits not configured for your account'
            );
        }

        // 🔥 1. Per Transaction Check
        if ($amount < $limit->per_transaction_min || $amount > $limit->per_transaction_max) {
            return ApiResponse::error(
                Response::HTTP_BAD_REQUEST,
                'Transaction amount must be between ' . $limit->per_transaction_min . ' and ' . $limit->per_transaction_max
            );
        }

        // 🔥 2. Daily হিসাব
        $today = Carbon::today();

        $dailyTransactions = $user->transactions()
            ->where('trx_type', $trxType)
            ->whereDate('created_at', $today);

        $dailyCount = $dailyTransactions->count();
        $dailySum = $dailyTransactions->sum('amount');

        if ($dailyCount >= $limit->daily_count) {
            return ApiResponse::error(
                Response::HTTP_BAD_REQUEST,
                'Daily transaction count exceeded'
            );
        }

        if (($dailySum + $amount) > $limit->daily_amount) {
            return ApiResponse::error(
                Response::HTTP_BAD_REQUEST,
                'Daily transaction amount exceeded'
            );
        }

        // 🔥 3. Monthly হিসাব
        $monthStart = Carbon::now()->startOfMonth();

        $monthlyTransactions = $user->transactions()
            ->where('trx_type', $trxType)
            ->where('created_at', '>=', $monthStart);

        $monthlyCount = $monthlyTransactions->count();
        $monthlySum = $monthlyTransactions->sum('amount');

        if ($monthlyCount >= $limit->monthly_count) {
            return ApiResponse::error(
                Response::HTTP_BAD_REQUEST,
                'Monthly transaction count exceeded'
            );
        }

        if (($monthlySum + $amount) > $limit->monthly_amount) {
            return ApiResponse::error(
                Response::HTTP_BAD_REQUEST,
                'Monthly transaction amount exceeded'
            );
        }

        return $next($request);
    }
}
