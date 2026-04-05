<?php

namespace Database\Factories;

use App\Models\SimOffer;
use App\Models\Admin\Admin;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SimOffer>
 */
class SimOfferFactory extends Factory
{
    protected $model = SimOffer::class;

    public function definition(): array
    {
        $start = $this->faker->dateTimeBetween('-10 days', '+5 days');
        $end = (clone $start)->modify('+30 days');

        return [
            'item_type' => $this->faker->randomElement(['regular']),
            'type' => $this->faker->randomElement(['data', 'minute', 'combo', 'other', 'collRate']),
            'operator' => $this->faker->randomElement(['Grameenphone', 'Robi', 'Airtel', 'Banglalink', 'Teletalk']),
            'title' => $this->faker->sentence(3),
            'code' => $this->faker->boolean(70) ? '*121#' : null,
            'validity' => $this->faker->randomElement(['1 Day', '3 Days', '7 Days', '30 Days']),
            'price' => $this->faker->randomFloat(2, 10, 999),
            'cashback' => $this->faker->boolean(40) ? $this->faker->randomFloat(2, 5, 200) : null,
            'discount' => $this->faker->boolean(40) ? $this->faker->randomFloat(2, 5, 100) : null,
            'details' => $this->faker->paragraph(),
            'tag' => $this->faker->randomElement(['Hot', 'Popular', 'Limited', 'New']),
            'activation_method' => $this->faker->randomElement([
                'Dial *121#',
                'Send SMS START to 121',
                'Recharge specific amount',
                'Auto activated on recharge',
                'Via operator app',
            ]),
            'eligibility' => $this->faker->sentence(),
            'terms_conditions' => $this->faker->paragraph(),
            'status' => $this->faker->randomElement(['active', 'inactive', 'expired']),

            'cashback_start_at' => $this->faker->boolean(50) ? $start : null,
            'cashback_hold' => $this->faker->boolean(30)
                ? $this->faker->randomFloat(2, 1, 30)
                : null,
            'cashback_end_at' => $this->faker->boolean(50) ? $end : null,

            'offer_start_at' => $start,
            'offer_end_at' => $end,
            'offer_stock' => $this->faker->numberBetween(0, 1000),

            'created_by' => Admin::inRandomOrder()->value('admin_id') ?? '8801775185654',
            'updated_by' => null,
        ];
    }

    /**
     * Active offer state
     */
    public function active()
    {
        return $this->state(fn() => [
            'status' => 'active',
        ]);
    }

    /**
     * Expired offer state
     */
    public function expired()
    {
        return $this->state(fn() => [
            'status' => 'expired',
            'active_end_at' => now()->subDays(5),
        ]);
    }
}