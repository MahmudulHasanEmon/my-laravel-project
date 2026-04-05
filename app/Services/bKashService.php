<?php

namespace App\Services;

use Exception;
use InvalidArgumentException;

class bKashService
{
  private const URL_TOKEN = "https://tokenized.pay.bka.sh/v1.2.0-beta/tokenized/checkout/token/grant";
  private const URL_PAYMENT = "https://tokenized.pay.bka.sh/v1.2.0-beta/tokenized/checkout/create";
  private const URL_EXECUTE = "https://tokenized.pay.bka.sh/v1.2.0-beta/tokenized/checkout/execute";

  private $appKey;
  private $appSecret;
  private $username;
  private $password;

  private $errorMessages = [
    2001 => "Invalid App Key",
    2002 => "Invalid Payment ID",
    2003 => "Process failed",
    // ... keep all other codes as in your original array
    503 => "System is undergoing maintenance. Please try again later."
  ];

  public function __construct()
  {
    // Use Laravel config
    $this->appKey = 'E612It2he1RylUlVMKynUy1Atc';
    $this->appSecret = 'TVWAgKFyzzbHTeZYigi1IuuOj8tTsgyiPq2WPFctUAkPoBkRsIQm';
    $this->username = '01775185654';
    $this->password = 'X3b>W!(Od+i';
  }

  public function getAccessToken(): string
  {
    $requestBody = json_encode([
      "app_key" => $this->appKey,
      "app_secret" => $this->appSecret,
    ]);

    $headers = $this->getHeaders();

    $response = $this->makeRequest(self::URL_TOKEN, $requestBody, $headers);

    if (isset($response['id_token'])) {
      return $response['id_token'];
    }

    throw new Exception("Access token fetch failed: " . json_encode($response));
  }

  public function createPayment(float $amount, int $userId, string $accessToken): array
  {
    if ($amount <= 0) {
      throw new InvalidArgumentException("Invalid amount. Must be positive.");
    }

    $requestBody = json_encode([
      "mode" => "0011",
      "amount" => $amount,
      "currency" => "BDT",
      "intent" => "sale",
      "payerReference" => '01',
      "merchantInvoiceNumber" => "My Offer- $userId",
      "callbackURL" => 'https://mahmudulhasanemon.com/myoffer/Utils/Sandbox/Callback.php' // Use Laravel route
    ]);

    $headers = $this->getHeaders($accessToken);

    return $this->makeRequest(self::URL_PAYMENT, $requestBody, $headers);
  }

  public function executePayment(string $paymentID, string $accessToken): array
  {
    if (empty($paymentID)) {
      throw new InvalidArgumentException("Payment ID cannot be empty.");
    }

    $requestBody = json_encode(["paymentID" => $paymentID]);

    $headers = $this->getHeaders($accessToken);

    return $this->makeRequest(self::URL_EXECUTE, $requestBody, $headers);
  }

  private function getHeaders(string $accessToken = null): array
  {
    $headers = [
      "Content-Type: application/json",
      "Accept: application/json",
      "X-App-Key: $this->appKey"
    ];

    if ($accessToken) {
      $headers[] = "Authorization: Bearer $accessToken";
    } else {
      $headers[] = "username: $this->username";
      $headers[] = "password: $this->password";
    }

    return $headers;
  }

  private function makeRequest(string $url, string $requestBody, array $headers): array
  {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_POST => true,
      CURLOPT_POSTFIELDS => $requestBody,
      CURLOPT_HTTPHEADER => $headers
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    curl_close($ch);

    if ($httpCode !== 200) {
      throw new Exception("HTTP Error: $httpCode, Response: $response");
    }

    return json_decode($response, true);
  }

  public function getErrorMessage(int $errorCode): string
  {
    return $this->errorMessages[$errorCode] ?? "Unknown error code: $errorCode";
  }
}