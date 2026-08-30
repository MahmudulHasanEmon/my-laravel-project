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
            ]
        );

    }


    public function getRechargeInfo(Request $request)
    {
        // 1️⃣ Manual Validator
        $validator = Validator::make($request->all(), [
            'operator' => 'required|string|max:15',
        ]);

        if ($validator->fails()) {
            \Log::info('Validation failed in getRechargeInfo:', $validator->errors()->toArray());

            return ApiResponse::error(
                StatusCode::UNPROCESSABLE_ENTITY,
                'Validation failed',
                $validator->errors()->toArray()
            );
        }

        $operator = $request->operator;

        // 2️⃣ Cache recharge plan for 24 hours
        $rechargePlan = MobileRecharge::where('operator', $operator)->first();

        // ⚠️ Check if $rechargePlan exists to avoid "Property on null" error
        if (!$rechargePlan) {
            return ApiResponse::error(
                StatusCode::NOT_FOUND,
                'Operator plan not found'
            );
        }

        // 3️⃣ Get offers if offer is active
        $offers = [];
        if ($rechargePlan->is_offer_active) {
            $offers = SimOffer::where('operator', $operator)
                ->where('status', 'active')
                ->where('item_type', 'regular')
                ->get();
        }

        // 4️⃣ User Balance (Optional chaining or auth safety check)
        $availableBalance = $request->user()?->available_balance ?? $request->user->available_balance;

        // 5️⃣ Fetch unique operators directly from DB (Optimized query)
        $operators = MobileRecharge::query()
            ->select('operator', 'logo_url')
            ->orderBy('operator', 'asc')
            ->get()
            ->unique('operator')
            ->values();



        // 6️⃣ Return success response
        return ApiResponse::success(
            StatusCode::OK,
            'Recharge information retrieved successfully.',
            [
                'available_balance' => $availableBalance,
                'recharge_plan' => $rechargePlan,
                'offers' => $offers,
                'operators' => $operators,
            ]
        );
    }

    public function getRechargeOperators()
    {

        $rechargePlans = MobileRecharge::query()
            ->select('operator', 'logo_url')
            ->orderBy('operator', 'asc')
            ->get()
            ->unique('operator')
            ->values(); // Index সঠিক রাখার জন্য values() ব্যবহার করা ভালো


        // 4️⃣ Return success response
        return ApiResponse::success(
            StatusCode::OK,
            'Recharge information retrieved successfully.',
            [
                'recharge_plan' => $rechargePlans,
            ]
        );
    }


}
