<?php

namespace Database\Factories\User;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User\Transaction>
 */
class TransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

    public function definition(): array
    {
        $type = $this->faker->randomElement(['credit', 'debit']);
        $amount = $this->faker->randomFloat(4, 10, 10000);
        $fee = $this->faker->randomFloat(4, 0, 50);
        $charge = $this->faker->randomFloat(4, 0, 20);
        $discount = $this->faker->randomFloat(4, 0, 10);

        $beforeBalance = $this->faker->randomFloat(4, 100, 50000);

        // balance logic
        $afterBalance = $type === 'credit'
            ? $beforeBalance + $amount
            : $beforeBalance - $amount;

        return [
            // 🔹 User সম্পর্ক
            'user_id' => '',
            // 🔹 Type
            'type' => $type,

            // 🔹 Transaction identity
            'trx_id' => strtoupper($this->faker->unique()->bothify('TRX#######')),
            'trx_type' => $this->faker->randomElement(['recharge', 'withdraw', 'transfer', 'payment']),

            // 🔹 Financial
            'amount' => $amount,
            'fee' => $fee,
            'charge' => $charge,
            'discount' => $discount,
            'currency' => 'BDT',

            // 🔹 Balance
            'balance_before' => $beforeBalance,
            'balance_after' => $afterBalance,

            // 🔹 Reference
            'trx_ref' => $this->faker->optional()->uuid(),

            // 🔹 Receiver (optional)
            'receiver' => $this->faker->phoneNumber(),
            'receiver_meta' => [
                'name' => $this->faker->name(),
                'phone' => $this->faker->phoneNumber(),
            ],

            // 🔹 Status
            'status' => $this->faker->randomElement([
                'pending',
                'processing',
                'completed',
                'failed'
            ]),

            // 🔹 Approval
            'approved_by' => null,
            'approved_at' => null,

            // 🔹 Flags
            'is_refundable' => $this->faker->boolean(30),
            'is_flagged' => $this->faker->boolean(10),

            // 🔹 Tracking
            'ip_address' => $this->faker->ipv4(),
            'device_info' => $this->faker->userAgent(),

            // 🔹 Notes
            'remarks' => $this->faker->optional()->sentence(),
        ];
    }

}
