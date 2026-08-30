<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Helpers\StatusCode;
use App\Http\Controllers\Controller;
use App\Models\SimOffer;
use Illuminate\Http\Request;

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
}