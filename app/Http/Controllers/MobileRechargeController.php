<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Helpers\StatusCode;
use App\Models\MobileRecharge;
use App\Models\SimOffer;
use App\Models\User\Transaction;
use App\Models\User\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use App\Helpers\User\TransactionCalculator;

class MobileRechargeController extends Controller
{

    public function recharge(Request $request)
    {
        // =========================================
        // Validation
        // =========================================

        $validator = Validator::make($request->all(), [
            'receiver' => 'required|string|max:20',
            'amount' => 'required|numeric|min:1',
            'operator' => 'required|string|max:15',
            'type' => 'required|string|in:prepaid,postpaid',
            'offer_id' => 'nullable|exists:sim_offers,id',
        ]);

        if ($validator->fails()) {

            return ApiResponse::error(
                StatusCode::UNPROCESSABLE_ENTITY,
                'Validation failed',
                $validator->errors()->toArray()
            );
        }

        // =========================================
        // Get User & Limit Data
        // =========================================

        $user = $request->user;

        if (!$user) {
            return ApiResponse::error(
                StatusCode::UNAUTHORIZED,
                'Unauthorized'
            );
        }

        $limit = $request->transaction_limit;

        if (!$limit) {
            return ApiResponse::error(
                StatusCode::BAD_REQUEST,
                'Transaction limit data not found'
            );
        }

        $amount = (float) $request->amount;

        // =========================================
        // Calculate Charges
        // =========================================

        $calculation = TransactionCalculator::calculate(
            $limit,
            $amount
        );

        // =========================================
        // Balance Check
        // =========================================

        if ($user->available_balance < $calculation['total_deduct']) {

            return ApiResponse::error(
                StatusCode::BAD_REQUEST,
                'Insufficient balance',
                [
                    'available_balance' => round($user->available_balance, 2),
                    'required_balance' => round($calculation['total_deduct'], 2),
                ]
            );
        }

        // =========================================
        // Balance Before
        // =========================================

        $balanceBefore = $user->available_balance;

        // =========================================
        // Deduct Balance
        // =========================================

        $user->available_balance =
            $user->available_balance - $calculation['total_deduct'];

        $user->save();

        // =========================================
        // Create Transaction
        // =========================================

        $transaction = Transaction::create([

            'user_id' => $user->user_id,

            'type' => 'debit',

            'trx_type' => 'mobile_recharge',

            'trx_id' => uniqid('trx_'),

            'fee' => $calculation['trx_fee'],

            'charge' => $calculation['charge'],

            'discount' => $calculation['commission'],

            'amount' => $amount,

            'total_amount' => $calculation['total_deduct'],

            'balance_before' => $balanceBefore,

            'balance_after' => $user->available_balance,

            'receiver' => $request->receiver,

            'receiver_meta' => [
                'operator' => $request->operator,
                'type' => $request->type,
                'offer_id' => $request->offer_id,
            ],

            'is_refundable' => true,

            'status' => 'pending',
        ]);

        // =========================================
        // Success Response
        // =========================================

        return ApiResponse::success(
            StatusCode::OK,
            'Recharge request submitted successfully',
            [
                'transaction' => $transaction,
                'calculation' => $calculation,
                'available_balance' => round($user->available_balance, 2),
            ]
        );

    }


    public function getRechargeInfo(Request $request)
    {
        // 1️⃣ Manual Validator for custom error handling
        $validator = Validator::make($request->all(), [
            'operator' => 'required|string|max:15',
        ]);

        if ($validator->fails()) {

            \Log::info('Validation failed in getRechargeInfo:', $validator->errors()->toArray());

            return ApiResponse::error(
                StatusCode::UNPROCESSABLE_ENTITY, // HTTP Unprocessable Entity
                'Validation failed',
                $validator->errors()->toArray()
            );
        }

        $operator = $request->operator;

        // cache key (unique per operator)
        $cacheKey = 'recharge_plan_' . $operator;

        // cache for 60 minutes
        $rechargePlan = Cache::remember($cacheKey, now()->addHours(24), function () use ($operator) {
            return MobileRecharge::where('operator', $operator)->first();
        });

        // 3️⃣ Check if offer is active
        $isOfferAvailable = $rechargePlan->is_offer_active;

        $offers = [];

        if ($isOfferAvailable) {

            // unique cache key
            $cacheKey = 'sim_offers_' . $operator;

            $offers = Cache::remember($cacheKey, now()->addHours(24), function () use ($operator) {
                return SimOffer::where('operator', $operator)
                    ->where('status', 'active')
                    ->where('item_type', 'regular')
                    ->get();
            });
        }

        $available_balance = $request->user->available_balance;

        // 4️⃣ Return success response
        return ApiResponse::success(
            StatusCode::OK,
            'Recharge information retrieved successfully.',
            [
                'available_balance' => $available_balance,
                'recharge_plan' => $rechargePlan,
                'offers' => $offers,

            ]
        );
    }


}
