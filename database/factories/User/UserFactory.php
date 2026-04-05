<?php

namespace Database\Factories\User;

use App\Models\User\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    
    protected static ?string $password;
    protected $model = User::class;

    public function definition(): array
    {
        return [
            // Identifiers
            'user_id' => $this->faker->unique()->numerify('88017########'),
            'referral_code' => strtoupper(Str::random(8)),

            // Personal Info
            'name' => fake()->name(),
            'father_name' => fake()->name('male'),
            'mother_name' => fake()->name('female'),
            'birthday' => fake()->date(),
            'gender' => fake()->randomElement(['male', 'female']),
            'marital_status' => fake()->randomElement(['single', 'married']),
            'nationality' => 'Bangladeshi',
            'occupation' => fake()->jobTitle(),
            'email' => fake()->unique()->safeEmail(),
            'contact_phone' => '8801' . fake()->numberBetween(300000000, 999999999),
            'income_source' => fake()->randomElement(['Job', 'Business', 'Freelance']),
            'nid_number' => fake()->unique()->numberBetween(1000000000, 9999999999),
            'address' => fake()->address(),

            // Security
            'password' => Hash::make('password'),
            'pin' => Hash::make('11223'),

            // Balances
            'available_balance' => fake()->randomFloat(2, 0, 50000),
            'hold_balance' => fake()->randomFloat(2, 0, 1000),
            'stock_balance' => fake()->randomFloat(2, 0, 10000),

            // Profile & Account
            'profile_url' => fake()->imageUrl(),
            'account_reference' => null,
            'account_id' => null,

            // Timestamps
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

}
