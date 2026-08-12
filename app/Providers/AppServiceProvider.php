<?php

namespace App\Providers;

use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot()
    {
        // Send OTP → প্রতি 2 মিনিটে 2 বার
        RateLimiter::for('send-otp', function (Request $request) {
            $key = $request->input('phone') ?: $request->ip();
            return Limit::perHour(100)->by($key)
                ->response(fn($req, $headers) => $this->rateLimitResponse('OTP requests', $headers, 120));
        });

        // Verify OTP → প্রতি 1 মিনিটে 3 বার
        RateLimiter::for('verify-otp', function (Request $request) {
            $key = $request->input('phone') ?: $request->ip();
            return Limit::perMinute(3)->by($key)
                ->response(fn($req, $headers) => $this->rateLimitResponse('OTP verification attempts', $headers, 60));
        });

        // PIN Login → প্রতি 1 মিনিটে 5 বার
        RateLimiter::for('pin-login', function (Request $request) {
            $key = $request->input('phone') ?: $request->ip();
            return Limit::perMinute(5)->by($key)
                ->response(fn($req, $headers) => $this->rateLimitResponse('PIN login attempts', $headers, 60));
        });

        // Register → প্রতি 10 মিনিটে 3 বার 
        RateLimiter::for('register', function (Request $request) {
            $key = $request->ip();
            return Limit::perMinutes(10, 100)->by($key)
                ->response(fn($req, $headers) => $this->rateLimitResponse('registration attempts', $headers, 600));
        });

        // Refresh Token → প্রতি 1 মিনিটে 10 বার
        RateLimiter::for('refresh-token', function (Request $request) {
            $key = $request->user()?->id ?: $request->ip();
            return Limit::perMinute(10)->by($key)
                ->response(fn($req, $headers) => $this->rateLimitResponse('token refresh requests', $headers, 60));
        });

    }

    /**
     * Helper for rate-limit response with proper wait time
     */
    protected function rateLimitResponse(string $action, array $headers, int $defaultRetryAfter)
    {
        $retry = $headers['Retry-After'] ?? $defaultRetryAfter;
        $m = intdiv($retry, 60);
        $s = $retry % 60;
        $wait = ($m ? "{$m} min " : '') . ($s ? "{$s} sec" : '');

        return response()->json([
            'success' => false,
            'message' => $action === 'OTP requests' ? "Please wait $wait before requesting a new OTP." : "Too many $action. Please wait $wait."
        ], 429, $headers);
    }

}