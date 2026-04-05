<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TransactionLimitController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user;
        $transactionLimits = $user->transactionLimits()->get();

        return response()->json([
            'status' => 'success',
            'data' => $transactionLimits
        ]);
    }
}
