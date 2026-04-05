<?php

namespace App\Helpers\User;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Support\Str;

class JwtHelper
{
  private static $key;
  private static $alg = "HS256";

  private static function init()
  {
    self::$key = env('USER_JWT_SECRET', config('app.key'));
  }

  
  public static function create(array $payload, int $expMinutes = 15, ?string $deviceId = null)
  {
    self::init();
    $jti = (string) Str::uuid();
    $now = time();
    $payload = array_merge($payload, [
      'jti' => $jti,
      'device_id' => $deviceId,
      'iat' => $now,
      'exp' => $now + ($expMinutes * 60)
    ]);
    $token = JWT::encode($payload, self::$key, self::$alg);
    return ['token' => $token, 'jti' => $jti, 'exp' => $payload['exp']];
  }

  public static function decode($jwt)
  {
    self::init();
    return JWT::decode($jwt, new Key(self::$key, self::$alg));
  }
}
