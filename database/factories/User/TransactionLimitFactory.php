<?php

namespace Database\Factories\User;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User\TransactionLimit>
 */

class TransactionLimitFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

    public function definition(): array
    {
        $trxTypes = ['send_money', 'mobile_recharge', 'pay_bill'];

        return [
            'role_id' => 1, // adjust based on your user_roles table
            'trx_type' => $this->faker->randomElement($trxTypes),

            'daily_count' => $this->faker->numberBetween(5, 20),
            'daily_amount' => $this->faker->numberBetween(1000, 50000),

            'monthly_count' => $this->faker->numberBetween(50, 500),
            'monthly_amount' => $this->faker->numberBetween(50000, 500000),

            'per_transaction_min' => $this->faker->numberBetween(10, 100),
            'per_transaction_max' => $this->faker->numberBetween(500, 20000),

            'charge_type' => $this->faker->randomElement(['fixed', 'percentage']),
            'charge_value' => $this->faker->randomFloat(2, 1, 5),

            'trx_fee' => $this->faker->numberBetween(5, 50),
            'trx_free_count' => $this->faker->numberBetween(0, 5),

            'commission_type' => $this->faker->randomElement(['fixed', 'percentage']),
            'commission_value' => $this->faker->randomFloat(2, 0.5, 3),
        ];
    }

}
