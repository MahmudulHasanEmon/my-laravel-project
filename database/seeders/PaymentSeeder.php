<?php

namespace Database\Seeders;

use App\Models\Admin\Admin;
use Illuminate\Database\Seeder;

class PaymentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\User\Payment::factory()->count(10)->create([
            'created_by' => Admin::inRandomOrder()->first()->admin_id,
            'logo_url' => 'https://iconape.com/wp-content/png_logo_vector/bkash-logo.png', // Placeholder image URL

        ]);
    }
}
