<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class ServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('ar_SA');
        $fakerEn = Faker::create('en_US');

        // Ensure we have at least one category
        if (Category::count() === 0) {
            Category::create([
                'name' => ['en' => 'General Services', 'ar' => 'خدمات عامة'],
                'description' => ['en' => 'General services category', 'ar' => 'تصنيف للخدمات العامة'],
                'status' => 'active',
                'image' => null,
            ]);
        }
        $categoryIds = Category::pluck('id')->toArray();

        // Ensure we have at least one user (vendor)
        $vendorIds = User::where('type', 'vendor')->pluck('id')->toArray();
        
        if (empty($vendorIds)) {
            $vendor = User::factory()->create([
                'type' => 'vendor',
                'name' => 'Test Vendor',
                'email' => 'vendor@example.com',
            ]);
            $vendorIds = [$vendor->id];
        }

        for ($i = 0; $i < 50; $i++) {
            Service::create([
                'category_id' => $faker->randomElement($categoryIds),
                'vendor_id' => $faker->randomElement($vendorIds),
                'title' => [
                    'en' => $fakerEn->words(3, true),
                    'ar' => $faker->realText(50),
                ],
                'description' => [
                    'en' => $fakerEn->paragraph(),
                    'ar' => $faker->realText(200),
                ],
                'price_type' => $faker->randomElement([Service::PRICE_TYPE_FIXED, Service::PRICE_TYPE_NEGOTIABLE]),
                'price' => $faker->randomFloat(2, 50, 5000),
                'status' => $faker->randomElement([Service::STATUS_ACTIVE, Service::STATUS_PENDING]),
                'admin_status' => 'approved',
                'published_at' => $faker->dateTimeBetween('-1 year', 'now'),
            ]);
        }
    }
}
