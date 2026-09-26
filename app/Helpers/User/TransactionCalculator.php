<?php

namespace App\Helpers\User;

class TransactionCalculator
{
  // This class can be used to handle any logic that needs to happen immediately after a transaction is created, such as:
  // - Sending notifications to the user
  // - Logging transaction details for auditing
  // - Triggering any post-transaction processes (e.g., updating related records, clearing caches, etc.)

  
  public static function calculate(array $limit, float $amount): array
  {
    // =========================================
    // Get Transaction Limit Data
    // =========================================

    $trxFee = (float) $limit['trx_fee'];

    $chargeType = $limit['charge_type'];
    $chargeValue = (float) $limit['charge_value'];

    $commissionType = $limit['commission_type'];
    $commissionValue = (float) $limit['commission_value'];

    // =========================================
    // Charge Calculation
    // =========================================
    
    $charge = 0;

    if ($chargeType === 'fixed') {

      $charge = $chargeValue;

    } elseif ($chargeType === 'percentage') {

      $charge = ($amount * $chargeValue) / 100;
    }

    // =========================================
    // Commission Calculation
    // =========================================

    $commission = 0;
    
    if ($commissionType === 'fixed') {

      $commission = $commissionValue;

    } elseif ($commissionType === 'percentage') {

      $commission = ($amount * $commissionValue) / 100;
    }

    // =========================================
    // Final Calculation
    // =========================================

    // Total Deduct From Sender
    $totalDeduct = $amount + $trxFee + $charge - $commission;
    
    // =========================================
    // Return Data
    // =========================================

    return [

      'amount' => round($amount, 2),

      'trx_fee' => round($trxFee, 2),
      'charge' => round($charge, 2),
      'commission' => round($commission, 2),

      'total_deduct' => round($totalDeduct, 2),
      
    ];
  }




}