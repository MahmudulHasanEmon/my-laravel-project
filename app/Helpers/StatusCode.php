<?php

namespace App\Helpers;

class StatusCode
{
  // Network / Device Errors
  public const NO_INTERNET = -1;
  public const CONNECTION_FAILED = -2;
  public const DNS_ERROR = -3;

  // Custom App-Specific
  public const USER_BLOCKED = 101;
  public const PAYMENT_PENDING = 102;
  public const NOT_VERIFIED = 103;
  public const SESSION_EXPIRED = 104;
  public const OTP_INVALID = 105;
  public const PROFILE_INCOMPLETE = 106;
  public const SUBSCRIPTION_EXPIRED = 107;
  public const FEATURE_DISABLED = 108;
  public const INVALID_PIN = 109;

  // CRUD
  public const CREATE_SUCCESS = 110;
  public const CREATE_FAILED = 111;
  public const READ_SUCCESS = 112;
  public const READ_FAILED = 113;
  public const UPDATE_SUCCESS = 114;
  public const UPDATE_FAILED = 115;
  public const DELETE_SUCCESS = 116;
  public const DELETE_FAILED = 117;

  // Success
  public const OK = 200;
  public const CREATED = 201;
  public const ACCEPTED = 202;
  public const NO_CONTENT = 204;

  // Client Errors
  public const BAD_REQUEST = 400;
  public const UNAUTHORIZED = 401;
  public const FORBIDDEN = 403;
  public const NOT_FOUND = 404;
  public const METHOD_NOT_ALLOWED = 405;
  public const REQUEST_TIMEOUT = 408;
  public const CONFLICT = 409;
  public const UNPROCESSABLE_ENTITY = 422;
  public const TOO_MANY_REQUESTS = 429;
  public const SSL_HANDSHAKE_FAILED = 495;
  public const TLS_ERROR = 496;
  public const CLIENT_CLOSED_REQUEST = 499;

  // Server Errors
  public const INTERNAL_SERVER_ERROR = 500;
  public const NOT_IMPLEMENTED = 501;
  public const BAD_GATEWAY = 502;
  public const SERVICE_UNAVAILABLE = 503;
  public const GATEWAY_TIMEOUT = 504;


  // =======================
  // Status code messages
  // =======================
  public static function message(int $code): string
  {
    return match ($code) {
        // Network / Device Errors
      self::NO_INTERNET => 'No internet connection.',
      self::CONNECTION_FAILED => 'Connection failed.',
      self::DNS_ERROR => 'DNS error occurred.',

        // Success
      self::OK => 'Request successful.',
      self::CREATED => 'Resource created successfully.',
      self::ACCEPTED => 'Request accepted.',
      self::NO_CONTENT => 'No content found.',

        // Client Errors
      self::BAD_REQUEST => 'Bad request.',
      self::UNAUTHORIZED => 'Unauthorized access.',
      self::FORBIDDEN => 'Access forbidden.',
      self::NOT_FOUND => 'Resource not found.',
      self::METHOD_NOT_ALLOWED => 'Method not allowed.',
      self::REQUEST_TIMEOUT => 'Request timeout.',
      self::CONFLICT => 'Conflict occurred.',
      self::UNPROCESSABLE_ENTITY => 'Unprocessable entity.',
      self::TOO_MANY_REQUESTS => 'Too many requests. Please try later.',
      self::SSL_HANDSHAKE_FAILED => 'SSL handshake failed.',
      self::TLS_ERROR => 'TLS error occurred.',
      self::CLIENT_CLOSED_REQUEST => 'Client closed the request.',

        // Server Errors
      self::INTERNAL_SERVER_ERROR => 'Internal server error.',
      self::NOT_IMPLEMENTED => 'Not implemented.',
      self::BAD_GATEWAY => 'Bad gateway.',
      self::SERVICE_UNAVAILABLE => 'Service unavailable.',
      self::GATEWAY_TIMEOUT => 'Gateway timeout.',

        // Custom App-Specific
      self::USER_BLOCKED => 'User is blocked.',
      self::PAYMENT_PENDING => 'Payment is pending.',
      self::NOT_VERIFIED => 'User not verified.',
      self::SESSION_EXPIRED => 'Session has expired.',
      self::OTP_INVALID => 'Invalid OTP.',
      self::PROFILE_INCOMPLETE => 'Profile is incomplete.',
      self::SUBSCRIPTION_EXPIRED => 'Subscription expired.',
      self::FEATURE_DISABLED => 'Feature is disabled.',
      self::INVALID_PIN => 'Invalid PIN.',

        // CRUD
      self::CREATE_SUCCESS => 'Resource created successfully.',
      self::CREATE_FAILED => 'Failed to create resource.',
      self::READ_SUCCESS => 'Resource read successfully.',
      self::READ_FAILED => 'Failed to read resource.',
      self::UPDATE_SUCCESS => 'Resource updated successfully.',
      self::UPDATE_FAILED => 'Failed to update resource.',
      self::DELETE_SUCCESS => 'Resource deleted successfully.',
      self::DELETE_FAILED => 'Failed to delete resource.',

      // Default fallback
      default => 'Unknown status code.',
    };
  }
}