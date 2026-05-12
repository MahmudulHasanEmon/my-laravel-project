<?php

namespace App\Http\Middleware;

use App\Helpers\ApiResponse;
use App\Helpers\StatusCode;
use App\Models\User\Transaction;
use App\Models\User\TransactionLimit;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UserCheckTransactionLimit
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, $trxType)
    {
        // =====================================
        // Auth User Check
        // =====================================

        $user = $request->user;

        if (!$user) {
            return ApiResponse::error(
                Response::HTTP_UNAUTHORIZED,
                'Unauthorized'
            );
        }

        // =====================================
        // Validate Amount
        // =====================================

        $amount = (float) $request->amount;

        if ($amount <= 0) {
            return ApiResponse::error(
                Response::HTTP_BAD_REQUEST,
                'Invalid transaction amount'
            );
        }

        // =====================================
        // Get User Role / Account Type
        // =====================================

        $roleId = $user->account_id;

        // =====================================
        // Get Transaction Limit Config
        // =====================================

        $limit = TransactionLimit::where('role_id', $roleId)
            ->where('trx_type', $trxType)
            ->first();

        if (!$limit) {
            return ApiResponse::error(
                Response::HTTP_FORBIDDEN,
                'Transaction limits are not configured for your account'
            );
        }

        // =====================================
        // 1. Per Transaction Limit Check
        // =====================================

        if (
            $amount < $limit->per_transaction_min ||
            $amount > $limit->per_transaction_max
        ) {
            return ApiResponse::error(
                Response::HTTP_BAD_REQUEST,
                'Transaction amount must be between '
                . number_format($limit->per_transaction_min, 2)
                . ' and '
                . number_format($limit->per_transaction_max, 2),
                [
                    'min_amount' => $limit->per_transaction_min,
                    'max_amount' => $limit->per_transaction_max,
                ]
            );
        }

        // =====================================
        // 2. Daily Transaction Check
        // =====================================

        $todayStart = Carbon::today();
        $todayEnd = Carbon::today()->endOfDay();

        $dailyTransactions = $user->transactions()
            ->where('trx_type', $trxType)
            ->whereBetween('created_at', [$todayStart, $todayEnd]);

        $dailyCount = (clone $dailyTransactions)->count();
        $dailySum = (clone $dailyTransactions)->sum('amount');

        // Daily Count Limit
        if ($dailyCount >= $limit->daily_count) {
            return ApiResponse::error(
                Response::HTTP_BAD_REQUEST,
                'Daily transaction count exceeded',
                [
                    'daily_limit' => $limit->daily_count,
                    'used' => $dailyCount,
                ]
            );
        }

        // Daily Amount Limit
        if (($dailySum + $amount) > $limit->daily_amount) {

            $remaining = max(
                0,
                $limit->daily_amount - $dailySum
            );

            return ApiResponse::error(
                Response::HTTP_BAD_REQUEST,
                'Daily transaction amount exceeded',
                [
                    'daily_limit' => $limit->daily_amount,
                    'used_amount' => $dailySum,
                    'remaining_amount' => $remaining,
                ]
            );
        }

        // =====================================
        // 3. Monthly Transaction Check
        // =====================================

        $monthStart = Carbon::now()->startOfMonth();
        $monthEnd = Carbon::now()->endOfMonth();

        $monthlyTransactions = $user->transactions()
            ->where('trx_type', $trxType)
            ->whereBetween('created_at', [$monthStart, $monthEnd]);

        $monthlyCount = (clone $monthlyTransactions)->count();
        $monthlySum = (clone $monthlyTransactions)->sum('amount');

        // Monthly Count Limit
        if ($monthlyCount >= $limit->monthly_count) {
            return ApiResponse::error(
                Response::HTTP_BAD_REQUEST,
                'Monthly transaction count exceeded',
                [
                    'monthly_limit' => $limit->monthly_count,
                    'used' => $monthlyCount,
                ]
            );
        }

        // Monthly Amount Limit
        if (($monthlySum + $amount) > $limit->monthly_amount) {

            $remaining = max(
                0,
                $limit->monthly_amount - $monthlySum
            );

            return ApiResponse::error(
                Response::HTTP_BAD_REQUEST,
                'Monthly transaction amount exceeded',
                [
                    'monthly_limit' => $limit->monthly_amount,
                    'used_amount' => $monthlySum,
                    'remaining_amount' => $remaining,
                ]
            );
        }

        // user balance check
        if ($user->available_balance < $amount) {
            return ApiResponse::error(
                Response::HTTP_BAD_REQUEST,
                'Insufficient balance',
                [
                    'current_balance' => number_format($user->available_balance, 2),
                    'required_balance' => number_format($amount, 2), // 100.00
                ]
            );
        }

        $receiver = $request->receiver;
        $userId = $user->user_id;

        if (empty($receiver)) {
            return ApiResponse::error(
                Response::HTTP_BAD_REQUEST,
                'Receiver information is required'
            );
        }

        $exists = Transaction::where('user_id', $userId)
            ->where('trx_type', $trxType)
            ->where('receiver', $receiver)
            ->where('amount', $amount)
            ->where(
                'created_at',
                '>=',
                Carbon::now()->subMinutes(10)
            )
            ->exists();

        if ($exists) {

            return ApiResponse::error(
                StatusCode::TOO_MANY_REQUESTS,
                'Same transaction blocked for 10 minutes'
            );
        }




        // =====================================
        // Attach Limit Config To Request
        // =====================================

        $limit = [
            'trx_fee' => (float) $monthlyCount >= $limit->trx_free_count ? $limit->trx_fee : 0,

            'charge_type' => $limit->charge_type,
            'charge_value' => (float) $limit->charge_value,

            'commission_type' => $limit->commission_type,
            'commission_value' => (float) $limit->commission_value,
        ];


        $request->merge([
            'transaction_limit' => $limit
        ]);

        return $next($request);
    }
}