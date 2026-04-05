<?php

namespace App\Http\Controllers;

use App\Models\SimOffer;
use App\Helpers\StatusCode;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use App\Models\MobileRecharge;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

class MobileRechargeController extends Controller
{
    // Controller methods would go here


    public function recharge(Request $request)
    {
        return response()->json([
            'status' => 'success',
            'data' => $request
        ]);
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
