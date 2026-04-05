<?php

namespace Database\Factories\User;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\User\UserSecurityPreference;

class UserSecurityPreferenceFactory extends Factory
{
    protected $model = UserSecurityPreference::class;

    public function definition(): array
    {
        return [            
            'user_id' => fake()->unique()->numberBetween(100000, 999999),
            'login_attempts' => 0,
            'pin_attempts' => 0,
            'is_locked' => false,
            'locked_at' => null,
            'locked_reason' => null,
            'last_failed_login' => null,
            'password_changed_at' => null,
            'pin_changed_at' => null,
            'two_factor_enabled_email' => fake()->boolean(30),
            'two_factor_enabled_phone' => fake()->boolean(40),
            'two_factor_secret' => null,
            'user_status' => true,
            'status_message' => null,
            'referral_code' => strtoupper(fake()->lexify('REF????')),
            'device_id' => fake()->uuid(),
            'device_type' => fake()->randomElement(['Android', 'iOS']),
            'device_name' => fake()->word(),
            'device_model' => fake()->randomElement(['Samsung S24', 'iPhone 15', 'Pixel 8']),
            'os' => fake()->randomElement(['Android', 'iOS']),
            'os_version' => fake()->randomElement(['14', '17']),
            'app_version' => '1.0.0',
            'device_fcm_token' => Str::random(32),
            'ip_address' => fake()->ipv4(),
            'location' => fake()->city(),
            'last_login_at' => now(),
            'last_activity_at' => now(),
            'is_trusted' => fake()->boolean(70),
            'is_blacklisted' => false,
            'remarks' => null,
            'timezone' => 'Asia/Dhaka',
            'notification_enabled' => true,
            'language_preference' => 'en',
            'theme_mode' => fake()->randomElement(['light', 'dark']),
            'created_at' => now(),
            'updated_at' => now(),
    
        ];
    }
}
