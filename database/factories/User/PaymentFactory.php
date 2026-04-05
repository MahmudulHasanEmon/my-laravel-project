<?php

namespace Database\Factories\User;

use App\Models\Admin\Admin;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User\Payment>
 */
class PaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'item_type' => $this->faker->randomElement(['personal', 'payment', 'agent']),
            'gateway_type' => $this->faker->randomElement(['manual', 'api']),
            'method' => $this->faker->randomElement(['bkash', 'nagad', 'rocket']),

            'phone' => $this->faker->phoneNumber(),
            'account_number' => $this->faker->bankAccountNumber(),
            'holder_name' => $this->faker->name(),
            'address' => $this->faker->address(),

            'username' => $this->faker->userName(),
            'password' => bcrypt('password'), // always hashed
            'app_key' => $this->faker->uuid(),
            'secret_key' => $this->faker->sha256(),

            'minimum_amount' => $this->faker->randomFloat(2, 10, 100),
            'maximum_amount' => $this->faker->randomFloat(2, 100, 10000),

            'is_active' => $this->faker->boolean(),

            'remarks' => $this->faker->sentence(),

            // ⚠️ Must match admins.admin_id
            'created_by' => '8801775185654', // Example admin_id, replace with actual admin_id from your database
            'updated_by' => null,
        ];
    }
}
