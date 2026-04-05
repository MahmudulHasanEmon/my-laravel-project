<?php

namespace App\Http\Controllers\User;

use App\Helpers\ApiResponse;
use App\Helpers\StatusCode;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    // create transaction
    public function create(Request $request)
    {

    }

    // get all transactions
    public function index(Request $request)
    {
        return ApiResponse::success(
            StatusCode::OK,
            'Last 5 transactions retrieved successfully',
            $request->user->transactions,
        );
    }


    // get last 5 transactions for authenticated user
    public function lastFive(Request $request)
    {
        try {
            $transactions = $request->user
                ->lastFiveTransactions;

            return ApiResponse::success(
                StatusCode::OK,
                'Last 5 transactions retrieved successfully',
                $transactions
            );

        } catch (\Exception $e) {
            return ApiResponse::error(
                StatusCode::INTERNAL_SERVER_ERROR,
                'Failed to fetch transactions',
                $e->getMessage()
            );
        }
    }

}
