<?php

namespace Database\Seeders;

use App\Models\Banner;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

class BannerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sourceDir = public_path('images/banners');
        $storagePath = 'banners';
        $filename = 'Artboard 1.png';

        // Ensure storage directory exists
        if (!Storage::disk('public')->exists($storagePath)) {
            Storage::disk('public')->makeDirectory($storagePath);
        }

        $sourcePath = $sourceDir . '/' . $filename;
        
        // Skip if file doesn't exist
        if (!file_exists($sourcePath)) {
            $this->command->warn("Banner file not found: {$filename}");
            return;
        }

        try {
            // Read the image file
            $imageContent = file_get_contents($sourcePath);
            $image = Image::make($imageContent);

            // Resize to 1200x600 like in BannerController
            $image->resize(1200, 600, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });

            // Generate unique filename
            $extension = pathinfo($filename, PATHINFO_EXTENSION);
            $newFilename = Str::uuid() . '.' . $extension;
            $filePath = $storagePath . '/' . $newFilename;

            // Save to storage
            Storage::disk('public')->put($filePath, $image->encode());

            // Create banner
            Banner::create([
                'image' => $filePath,
                'link' => null,
                'status' => Banner::STATUS_ACTIVE,
                'order' => 1,
            ]);

            $this->command->info("Created banner successfully");
        } catch (\Exception $e) {
            $this->command->error("Failed to process banner: " . $e->getMessage());
        }
    }
}
