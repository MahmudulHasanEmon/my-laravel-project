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

        return ApiResponse::success(
            StatusCode::OK,
            'Transaction created successfully',
            $request->all()
        );

    }


    // get all transactions with pagination
    public function transactions(Request $request)
    {
        // প্রতি পেজে কতটি ডেটা দেখাবেন (ডিফল্ট ১০ বা আপনার প্রয়োজন অনুযায়ী)
        $perPage = $request->get('per_page', 25);

        // পেজিনেশন কুয়েরি (নতুন ট্রানজেকশন আগে দেখানোর জন্য latest() যুক্ত করতে পারেন)
        $paginator = $request->user()->transactions()
            ->latest()
            ->paginate(250);

        // নিজের পছন্দমতো কাস্টম স্ট্রাকচার তৈরি করা
        $customResponse = [
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
            'data' => $paginator->items(), // মূল ট্রানজেকশন লিস্ট
        ];

        return ApiResponse::success(
            StatusCode::OK,
            'Transactions retrieved successfully',
            $customResponse
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
