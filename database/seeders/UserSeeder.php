<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        
        $user = User::updateOrCreate(
            ['email' => "user@test.com"],
            [
                'first_name' => "User",
                'last_name' => "Test",
                'phone' => '966596000912',
                'password' => Hash::make('12312345'),
                'type' => 'user',
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );
        $vendor = User::updateOrCreate(
            ['email' => 'vendor@test.com'],
            [
                'first_name' => 'Hashim',
                'last_name' => 'Vendor',
                'phone' => '966539796901',
                'password' => Hash::make('12312345'),
                'type' => 'vendor',
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );
        $users = [
            [
                'first_name' => 'Admin',
                'email' => 'admin@admin.com',
                'type' => 'admin',
                'password' => bcrypt('password'),
            ],
            [
                'first_name' => 'User',
                'email' => 'user@user.com',
                'type' => 'user',
                'password' => bcrypt('password'),
            ],
            [
                'first_name' => 'Vendor',
                'email' => 'vendor@vendor.com',
                'type' => 'vendor',
                'password' => bcrypt('password'),
            ],
        ];

        DB::table('users')->insert($users);
    }
}
