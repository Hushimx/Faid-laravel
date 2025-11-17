<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
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
