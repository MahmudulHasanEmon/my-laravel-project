<?php

namespace Database\Factories\Admin;

use App\Models\Admin\Admin;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class AdminFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

    protected $model = Admin::class;
    public function definition(): array
    {
        return [
            'admin_id' => $this->faker->unique()->numerify('88017########'),
            'name' => $this->faker->name(),
            'password' => 'password', // default password
            'available_balance' => $this->faker->randomFloat(2, 0, 10000),
            'hold_balance' => $this->faker->randomFloat(2, 0, 5000),
            'stock_balance' => $this->faker->randomFloat(2, 0, 1000),
            'profile_url' => $this->faker->imageUrl(200, 200, 'people'),
            'fcm_token' => null,
            'app_version' => '1.0.0',
            'admin_status' => true,
            'status_message' => null,
            'device_id' => Str::uuid(),
            'device_info' => $this->faker->userAgent(),
            'ip_address' => $this->faker->ipv4(),
            'last_login_at' => now(),
            'last_activity_at' => now(),
            'login_attempts' => 0,
            'is_locked' => false,
            'locked_at' => null,
            'password_changed_at' => now(),
            'two_factor_enabled' => false,
            'two_factor_secret' => null,
            'notification_enabled' => true,
            'notification_category' => null,
            'language_preference' => 'en',
            'theme_mode' => 'light',
            'nid_number' => $this->faker->numerify('###########'),
            'verification_doc' => null,
            'verified_at' => now(),
            'remarks' => null,
            'timezone' => 'Asia/Dhaka',
        ];
    }
}
