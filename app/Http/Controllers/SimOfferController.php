<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Helpers\StatusCode;
use App\Helpers\User\TransactionCalculator;
use App\Http\Controllers\Controller;
use App\Models\SimOffer;
use App\Models\User\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SimOfferController extends Controller
{
    public function allOffers(Request $request)
    {
        // Fetch active SIM offers ordered by price
        $offers = SimOffer::where('status', 'active')
            //->orderBy('price', 'asc')
            ->get();

        return ApiResponse::success(
            StatusCode::OK,
            'Active SIM offers retrieved successfully.',
            $offers->toArray() // Convert Eloquent collection to array
        );
    }



    public function offerPurchase(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'offer_id' => 'required|integer|exists:sim_offers,id',
            'receiver' => 'required|string|max:20',

        ]);


        if ($validator->fails()) {
            return ApiResponse::error(
                StatusCode::UNPROCESSABLE_ENTITY,
                'Validation failed',
                $validator->errors()->toArray()
            );
        }

        // Fetch the offer by ID
        $offer = SimOffer::find($request->input('offer_id'));

        if (!$offer) {
            return ApiResponse::error(
                StatusCode::NOT_FOUND,
                'Offer not found.'
            );
        }

        // Check if the offer is active
        if ($offer->status !== 'active') {
            return ApiResponse::error(
                StatusCode::BAD_REQUEST,
                'This offer is not currently ' . $offer->status . '.'
            );
        }

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

        $calculation = TransactionCalculator::calculate(
            $limit,
            ($offer->price - $offer->discount)
        );

        // =========================================
        // Balance Check
        // =========================================

        if ($user->available_balance < ($calculation['total_deduct']) - $offer->discount) {
            return ApiResponse::error(
                StatusCode::BAD_REQUEST,
                'Insufficient balance',
                [
                    'available_balance' => round($user->available_balance, 2),
                    'required_balance' => round(($calculation['total_deduct'] - $offer->discount), 2),
                ]
            );
        }


        $balanceBefore = $user->available_balance;

        // =========================================
        // Deduct Balance
        // =========================================

        $user->available_balance = $user->available_balance - ($calculation['total_deduct'] - $offer->discount);
        $user->save();


        // =========================================
        // Create Transaction
        // =========================================

        $transaction = Transaction::create([
            'user_id' => $user->user_id,
            'type' => 'debit',
            'trx_type' => 'offer_purchase',
            'trx_id' => uniqid('trx_'),
            'fee' => $calculation['trx_fee'],
            'charge' => $calculation['charge'],
            'discount' => ($calculation['commission'] + $offer->discount),
            'amount' => $offer->price - $offer->discount,
            'total_amount' => $calculation['total_deduct'],
            'balance_before' => $balanceBefore,
            'balance_after' => $user->available_balance,
            'receiver' => $request->receiver,
            'receiver_meta' => [
                'operator' => $offer->operator,
                'offer' => $offer,
            ],
            'is_refundable' => true,
            'status' => 'pending', // Data Truncated এরর সমাধানের জন্য এটি যুক্ত করা হয়েছে
        ]);

        // =========================================
        // Success Response
        // =========================================

        return ApiResponse::success(
            StatusCode::OK,
            'Offer purchase request submitted successfully',
            [
                'transaction' => $transaction,
                'offer' => $offer,
            ]
        );


    }



}