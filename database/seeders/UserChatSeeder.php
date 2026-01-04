<?php

namespace Database\Seeders;

use App\Models\Chat;
use App\Models\Message;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserChatSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create the vendor user with specified credentials
        $vendor = User::updateOrCreate(
            ['email' => 'hashimmeko6666@gmail.com'],
            [
                'first_name' => 'Hashim',
                'last_name' => 'Vendor',
                'phone' => '966596000912',
                'password' => Hash::make('12312345'),
                'type' => 'vendor',
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        // Create regular users
        $users = [];
        for ($i = 1; $i <= 5; $i++) {
            $user = User::updateOrCreate(
                ['email' => "user{$i}@example.com"],
                [
                    'first_name' => "User{$i}",
                    'last_name' => "Test",
                    'phone' => '96650000000' . $i,
                    'password' => Hash::make('password'),
                    'type' => 'user',
                    'status' => 'active',
                    'email_verified_at' => now(),
                ]
            );
            $users[] = $user;
        }

        // Get a category (assuming categories are already seeded)
        $category = DB::table('categories')->first();
        if (!$category) {
            $this->command->warn('No categories found. Please run CategorySeeder first.');
            return;
        }

        // Create services for the vendor
        $services = [];
        $serviceTitles = [
            ['en' => 'Plumbing Service', 'ar' => 'خدمة السباكة'],
            ['en' => 'Electrical Service', 'ar' => 'خدمة الكهرباء'],
            ['en' => 'Cleaning Service', 'ar' => 'خدمة التنظيف'],
        ];

        foreach ($serviceTitles as $index => $title) {
            $service = new Service();
            $service->category_id = $category->id;
            $service->vendor_id = $vendor->id;
            $service->title = normalize_translations([
                'en' => $title['en'],
                'ar' => $title['ar'],
            ]);
            $service->description = normalize_translations([
                'en' => "Professional {$title['en']} with years of experience.",
                'ar' => "{$title['ar']} احترافية مع سنوات من الخبرة.",
            ]);
            $service->price_type = 'fixed';
            $service->price = rand(100, 500);
            $service->status = 'active';
            $service->published_at = now();
            $service->save();
            $services[] = $service;
        }

        // Create chats between users and vendor
        $messages = [
            ['Hello, I am interested in your service.', 'مرحبا، أنا مهتم بخدمتك.'],
            ['Can you provide more details?', 'هل يمكنك تقديم المزيد من التفاصيل؟'],
            ['What is the price?', 'ما هو السعر؟'],
            ['When can you start?', 'متى يمكنك البدء؟'],
            ['Thank you for your response.', 'شكرا لردك.'],
        ];

        foreach ($users as $userIndex => $user) {
            // Create a chat for each service with different users
            $service = $services[$userIndex % count($services)];
            
            $chat = Chat::firstOrCreate(
                [
                    'user_id' => $user->id,
                    'vendor_id' => $vendor->id,
                    'service_id' => $service->id,
                ]
            );

            // Create messages in the chat
            $messageCount = rand(3, 5);
            for ($i = 0; $i < $messageCount; $i++) {
                $isUserMessage = $i % 2 === 0; // Alternate between user and vendor
                $senderId = $isUserMessage ? $user->id : $vendor->id;
                $messageText = $messages[$i % count($messages)][$isUserMessage ? 0 : 1];

                Message::create([
                    'chat_id' => $chat->id,
                    'sender_id' => $senderId,
                    'message_type' => 'text',
                    'message' => $messageText,
                    'created_at' => now()->subMinutes($messageCount - $i),
                    'updated_at' => now()->subMinutes($messageCount - $i),
                ]);
            }
        }

        $this->command->info('Users, services, chats, and messages seeded successfully!');
        $this->command->info("Vendor credentials:");
        $this->command->info("Email: hashimmeko6666@gmail.com");
        $this->command->info("Password: 12312345");
        $this->command->info("Phone: 966596000912");
    }
}

