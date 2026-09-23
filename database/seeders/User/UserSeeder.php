<?php

namespace Database\Seeders\User;

use App\Models\User\Transaction;
use App\Models\User\TransactionLimit;
use App\Models\User\User;
use App\Models\User\UserRole;
use Illuminate\Database\Seeder;
use App\Models\User\UserPermission;
use Illuminate\Support\Facades\Hash;
use App\Models\User\UserSecurityPreference;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $personal = UserRole::create([
            'name' => 'personal',
            'display_name' => 'Personal',
            'description' => 'Has basic user permissions',
            'is_active' => true,
        ]);


        $permissions = [
            [
                'name' => 'add_money',
                'display_name' => 'Add Money',
                'module' => 'Account Management',
                'show_in' => 'dashboard',
                'description' => 'Allows adding money to user account.',
                'is_active' => true,
                'priority' => 1,
                'is_hidden' => false,
            ],
            [
                'name' => 'mobile_recharge',
                'display_name' => 'Recharge Mobile',
                'module' => 'Recharge Management',
                'show_in' => 'dashboard',
                'description' => 'Allows recharging of mobile phones.',
                'is_active' => true,
                'priority' => 2,
                'is_hidden' => false,
            ],
            [
                'name' => 'send_money',
                'display_name' => 'Send Money',
                'module' => 'Transaction Management',
                'show_in' => 'dashboard',
                'description' => 'Allows sending money to other users.',
                'is_active' => true,
                'priority' => 3,
                'is_hidden' => false,
            ],
            [
                'name' => 'bill_payment',
                'display_name' => 'Bill Payment',
                'module' => 'Account Management',
                'show_in' => 'dashboard',
                'description' => 'Allows payment of utility bills.',
                'is_active' => true,
                'priority' => 4,
                'is_hidden' => false,
            ],
            [
                'name' => 'bank_transfer',
                'display_name' => 'Bank Transfer',
                'module' => 'Transaction Management',
                'show_in' => 'dashboard',
                'description' => 'Allows bank transfers.',
                'is_active' => true,
                'priority' => 5,
                'is_hidden' => false,
            ],
            [
                'name' => 'sim_offer',
                'display_name' => 'SIM Offer',
                'module' => 'Promotion Management',
                'show_in' => 'dashboard',
                'description' => 'Allows viewing and availing SIM offers.',
                'is_active' => true,
                'priority' => 6,
                'is_hidden' => false,
            ],


            // Add more permissions here
        ];

        foreach ($permissions as $perm) {
            $p = UserPermission::create($perm);
            $personal->permissions()->attach($p->id);
        }

        // 3️⃣ Create a User
        User::factory()->create(
            [
                'user_id' => '8801775185654',
                'name' => 'MD. Mahmudul Hasan Emon',
                'pin' => Hash::make('87113'),
                'account_id' => $personal->id,
                'profile_url' => 'https://images.pexels.com/photos/8575861/pexels-photo-8575861.jpeg',
            ]
        )->each(function ($user) {
            UserSecurityPreference::factory()->create([
                'user_id' => $user->user_id,
                'device_id' => fake()->uuid(),
                'device_type' => 'Android',
                'device_name' => fake()->word(),
                'device_model' => 'Samsung S24',
                'os' => 'Android',
                'os_version' => '14',
            ]);

            Transaction::factory()->count(200)->create(
                [
                    'user_id' => $user->user_id,
                ]
            );

        });// 3️⃣ Create a User
        User::factory()->create(
            [
                'user_id' => '8801845416702',
                'name' => 'MD. Mahmudul',
                'pin' => Hash::make('87113'),
                'account_id' => $personal->id,
                'profile_url' => 'https://images.pexels.com/photos/8575861/pexels-photo-8575861.jpeg',

            ]
        )->each(function ($user) {
            UserSecurityPreference::factory()->create([
                'user_id' => $user->user_id,
                'device_id' => fake()->uuid(),
                'device_type' => 'Android',
                'device_name' => fake()->word(),
                'device_model' => 'Samsung S24',
                'os' => 'Android',
                'os_version' => '14',
            ]);
            Transaction::factory()->count(1000)->create(
                [
                    'user_id' => $user->user_id,
                ]
            );

        });


        $trxTypes = ['send_money', 'mobile_recharge', 'pay_bill'];

        foreach ($trxTypes as $type) {
            TransactionLimit::factory()->create([
                'trx_type' => $type,
            ]);
        }


    }
}
