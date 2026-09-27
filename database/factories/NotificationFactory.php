<?php

namespace Database\Factories;

use App\Models\Admin\Admin;
use App\Models\Notification;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class NotificationFactory extends Factory
{
    protected $model = Notification::class;

    public function definition(): array
    {
        // Define your custom image URLs in an array
        $imageUrls = [
            'https://www.grameenphone.com/_next/image?url=https%3A%2F%2Fcdn01da.grameenphone.com%2Fsites%2Fdefault%2Ffiles%2F2026-02%2FWeb-1920x464_0.jpg&w=1920&q=75',
            'https://www.grameenphone.com/_next/image?url=https%3A%2F%2Fcdn01da.grameenphone.com%2Fsites%2Fdefault%2Ffiles%2F2026-05%2FLong%20Validity_Web-1920x464_2.jpg&w=1920&q=75',
            'https://www.grameenphone.com/_next/image?url=https%3A%2F%2Fcdn01da.grameenphone.com%2Fsites%2Fdefault%2Ffiles%2F2026-07%2F1920X464_2.jpg&w=1920&q=75',
        ];

        return [
            // ইউজার টেবিল থেকে র্যান্ডম user_id নেওয়া (অথবা চাইলে null রাখতে পারেন)
            'user_id' => User::inRandomOrder()->value('user_id') ?? null,

            'recipient_type' => $this->faker->randomElement(['admin', 'user', 'vip']),
            'title' => $this->faker->sentence(4),
            'body' => $this->faker->paragraph(),

            // Randomly pick one of your URLs, or make it optionally null
            'image_url' => $this->faker->optional(0.8)->randomElement($imageUrls),

            'type' => $this->faker->randomElement(['transaction', 'system', 'offer', 'alert']),

            // মাইগ্রেশনে থাকা চ্যানেলগুলোর মধ্য থেকে র্যান্ডম একটি সিলেক্ট হবে
            'channel' => $this->faker->randomElement([
                'PUSH',
                'SLIDER'
            ]),

            'action_url' => $this->faker->optional()->url(),
            'priority' => $this->faker->randomElement(['low', 'normal', 'high']),
            'sender' => 'system',

            'seen' => $this->faker->boolean(30), // ৩০% সম্ভাবনা রয়েছে দেখার
            'delivered' => $this->faker->boolean(80),

            'expire_at' => $this->faker->optional()->dateTimeBetween('+1 days', '+30 days'),

            'created_by' => 'system',
            // 'updated_by' => Admin::inRandomOrder()->value('admin_id') ?? null,
        ];
    }
}