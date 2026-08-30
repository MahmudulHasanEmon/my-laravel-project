<?php

namespace Database\Factories;

use App\Models\Admin\Admin;
use App\Models\MobileRecharge;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MobileRecharge>
 */
class MobileRechargeFactory extends Factory
{
    protected $model = MobileRecharge::class;

    public function definition(): array
    {
        return [
            'type' => $this->faker->randomElement(['prepaid', 'postpaid']),
            'operator' => $this->faker->randomElement([
                'Grameenphone',
                'Robi',
                'Airtel',
                'Banglalink',
                'Teletalk'
            ]),
            'request_type' => $this->faker->randomElement([
                'api',
                'ussd',
                'manual'
            ]),

            
            'block_amount' => [
                'today' => $this->faker->numberBetween(0, 500),
                'this_week' => $this->faker->numberBetween(0, 2000),
                'this_month' => $this->faker->numberBetween(0, 5000),
            ],

            'pending_amount' => [
                'pending_today' => $this->faker->numberBetween(0, 300),
                'pending_total' => $this->faker->numberBetween(0, 4000),
            ],

            'ussd' => $this->faker->randomElement([
                '*121#',
                '*123#',
                '*222#'
            ]),

            'balance' => $this->faker->randomFloat(2, 0, 50000),

            'is_pending' => $this->faker->boolean(20),
            'is_active' => $this->faker->boolean(90),
            'is_offer_active' => $this->faker->boolean(80),
            
            'minimum_amount' => $this->faker->randomFloat(2, 10, 50),
            'maximum_amount' => $this->faker->randomFloat(2, 100, 2000),
            'logo_url' => 'https://images.seeklogo.com/logo-png/24/1/grameenphone-logo-png_seeklogo-249793.png',
            'created_by' => Admin::inRandomOrder()->value('admin_id') ?? 1,
            'updated_by' => null,
        ];
    }

    /**
     * Active recharge state
     */
    public function active()
    {
        return $this->state(fn() => [
            'is_active' => true,
        ]);
    }

    /**
     * Pending recharge state
     */
    public function pending()
    {
        return $this->state(fn() => [
            'is_pending' => true,
        ]);
    }
}