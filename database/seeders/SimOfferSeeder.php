<?php

namespace Database\Seeders;


use Illuminate\Database\Seeder;

class SimOfferSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\SimOffer::factory()->count(500)->create();

        $types = [
            'prepaid',
            'postpaid',
        ];

        $operators = [
            'Grameenphone',
            'Robi',
            'Airtel',
            'Banglalink',
            'Teletalk',
            'Skitto',
            'Citycell',
        ];

        $requestTypes = [
            'api',
            'ussd',
            'manual',
        ];

        foreach ($types as $type) {
            foreach ($operators as $operator) {
                foreach ($requestTypes as $requestType) {

                    \App\Models\MobileRecharge::factory()
                        ->state([
                            'type' => $type,
                            'operator' => $operator,
                            'request_type' => $requestType,
                        ])
                        ->create();
                }
            }
        }

    }
}
