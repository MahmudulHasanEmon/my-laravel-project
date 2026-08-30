<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Helpers\StatusCode;
use App\Models\User\Payment;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    // get payment info
    public function getPaymentInfo(Request $request)
    {
        // For demonstration, we'll return a static response.
        // In a real application, you would fetch this data from the database or an external API.

        $paymentInfo = Payment::where('is_active', true)->get();

        return ApiResponse::success(
            StatusCode::OK,
            'Payment information retrieved successfully.',
            $paymentInfo
        );

    }

    public function loadBkashPayment(Request $request)
    {
        // This method would contain logic to fetch and return bKash payment information.
        // For demonstration, we'll return a static response.

        $bkashInfo = [
            'name' => 'bKash',
            'logo_url' => 'https://iconape.com/wp-content/png_logo_vector/bkash-logo.png',
            'description' => 'bKash is a leading mobile financial service in Bangladesh.',
            'contact' => '123-456-7890',
        ];

        return ApiResponse::success(
            StatusCode::OK,
            'bKash payment information retrieved successfully.',
            $bkashInfo
        );





    }


    public function createPayment(Request $request)
    {
        $bkash = new \App\Services\bKashService();

        $amount = '100'; // Replace with actual amount from request
        $userId = '01';  // Replace with actual user ID

        try {
            $accessToken = $bkash->getAccessToken();
            $payment = $bkash->createPayment($amount, $userId, $accessToken);
            
            $url = $payment['bkashURL'] ?? null;

            if (!$url) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create bKash payment. No URL returned.'
                ], 500);
            }

            // Redirect user to the bKash payment page
            return redirect()->away($url);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }


}
