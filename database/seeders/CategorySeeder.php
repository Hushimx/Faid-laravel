<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Map Arabic filenames to category data
        $categories = [
            'انظمة المراقبة.png' => [
                'name' => ['en' => 'Surveillance Systems', 'ar' => 'أنظمة المراقبة'],
                'description' => ['en' => 'Security and surveillance systems', 'ar' => 'أنظمة الأمن والمراقبة'],
            ],
            'نقل الأثاث.png' => [
                'name' => ['en' => 'Furniture Moving', 'ar' => 'نقل الأثاث'],
                'description' => ['en' => 'Professional furniture moving services', 'ar' => 'خدمات نقل الأثاث المحترفة'],
            ],
            'خدمات التكييف.png' => [
                'name' => ['en' => 'Air Conditioning Services', 'ar' => 'خدمات التكييف'],
                'description' => ['en' => 'Air conditioning installation and maintenance', 'ar' => 'تركيب وصيانة أنظمة التكييف'],
            ],
            'تاجير السيارات.png' => [
                'name' => ['en' => 'Car Rental', 'ar' => 'تأجير السيارات'],
                'description' => ['en' => 'Car and vehicle rental services', 'ar' => 'خدمات تأجير السيارات والمركبات'],
            ],
            'خدمات التنظييف.png' => [
                'name' => ['en' => 'Cleaning Services', 'ar' => 'خدمات التنظيف'],
                'description' => ['en' => 'Professional cleaning and maintenance services', 'ar' => 'خدمات التنظيف والصيانة المحترفة'],
            ],
            'مكافحة الحشرات.png' => [
                'name' => ['en' => 'Pest Control', 'ar' => 'مكافحة الحشرات'],
                'description' => ['en' => 'Pest control and extermination services', 'ar' => 'خدمات مكافحة الحشرات والقضاء عليها'],
            ],
            'الأقفال والمفاتيح.png' => [
                'name' => ['en' => 'Locks and Keys', 'ar' => 'الأقفال والمفاتيح'],
                'description' => ['en' => 'Locksmith services and key duplication', 'ar' => 'خدمات الأقفال والمفاتيح وتكرار المفاتيح'],
            ],
            'قطع غيار السيارات.png' => [
                'name' => ['en' => 'Car Parts', 'ar' => 'قطع غيار السيارات'],
                'description' => ['en' => 'Automotive parts and accessories', 'ar' => 'قطع غيار وملحقات السيارات'],
            ],
            'المقاولات.png' => [
                'name' => ['en' => 'Contracting', 'ar' => 'المقاولات'],
                'description' => ['en' => 'Construction and contracting services', 'ar' => 'خدمات البناء والمقاولات'],
            ],
            'الحرف والصيانة المنزلية.png' => [
                'name' => ['en' => 'Handicrafts and Home Maintenance', 'ar' => 'الحرف والصيانة المنزلية'],
                'description' => ['en' => 'Handicrafts and home maintenance services', 'ar' => 'خدمات الحرف اليدوية والصيانة المنزلية'],
            ],
        ];

        $sourceDir = public_path('images/categories');
        $storagePath = 'categories';

        // Ensure storage directory exists
        if (!Storage::disk('public')->exists($storagePath)) {
            Storage::disk('public')->makeDirectory($storagePath);
        }

        foreach ($categories as $filename => $data) {
            $sourcePath = $sourceDir . '/' . $filename;
            
            // Skip if file doesn't exist
            if (!file_exists($sourcePath)) {
                $this->command->warn("File not found: {$filename}");
                continue;
            }

            try {
                // Read the image file
                $imageContent = file_get_contents($sourcePath);
                $image = Image::make($imageContent);

                // Resize to 600x600 like in CategoryController
                $image->resize(600, 600, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });

                // Generate unique filename
                $extension = pathinfo($filename, PATHINFO_EXTENSION);
                $newFilename = Str::uuid() . '.' . $extension;
                $filePath = $storagePath . '/' . $newFilename;

                // Save to storage
                Storage::disk('public')->put($filePath, $image->encode());

                // Create category
                Category::create([
                    'name' => normalize_translations($data['name']),
                    'description' => normalize_translations($data['description']),
                    'image' => $filePath,
                    'status' => Category::STATUS_ACTIVE,
                ]);

                $this->command->info("Created category: {$data['name']['ar']}");
            } catch (\Exception $e) {
                $this->command->error("Failed to process {$filename}: " . $e->getMessage());
            }
        }
    }
}
