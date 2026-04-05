<?php

namespace App\Helpers;

use Illuminate\Http\JsonResponse;

class ApiResponse
{
  /**
   * ✅ Standard Success Response
   *
   * @param  int         $statusCode
   * @param  string      $message
   * @param  mixed|null  $data
   * @param  array       $meta
   * @return JsonResponse
   */
  
  public static function success(
    int $statusCode = 200,
    string $message = 'Success',
    mixed $data = null,
    array $meta = []
  ): JsonResponse {
    $response = [
      'success' => true,
      'message' => $message,
      'status_code' => $statusCode,
      // 'meta' => array_merge([
      //   'status_code' => $statusCode,
      //   'timestamp' => now()->toISOString(),
      // ], $meta),
    ];

    if (!is_null($data)) {
      $response['data'] = $data;
    }

    return response()->json($response, $statusCode);
  }

  /**
   * ❌ Standard Error Response
   *
   * @param  int         $statusCode
   * @param  string      $message
   * @param  mixed|null  $errors
   * @param  array       $meta
   * @return JsonResponse
   */
  public static function error(
    int $statusCode = 400,
    string $message = 'Something went wrong',
    mixed $errors = null,
    array $meta = []
  ): JsonResponse {
    $response = [
      'success' => false,
      'message' => $message,
      'status_code' => $statusCode,
      // 'meta' => array_merge([
      //   'status_code' => $statusCode,
      //   'timestamp' => now()->toISOString(),
      // ], $meta),
    ];

    if (!is_null($errors)) {
      $response['errors'] = $errors;
    }

    return response()->json($response, $statusCode);
  }
}