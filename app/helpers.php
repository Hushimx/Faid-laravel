<?php

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

if (!function_exists('HandleActiveSidebar')) {
    function handleActiveSidebar(array $routes)
    {
        foreach ($routes as $route) {
            if (\Illuminate\Support\Facades\Route::is(trim($route))) {
                return 'active';
            }
        }
    }
}

if (!function_exists('uploadFile')) {
    /**
     * Upload a file to storage
     *
     * @param UploadedFile $file The uploaded file
     * @param string $path The storage path (e.g., 'users/avatars')
     * @param string|null $oldFile Path of old file to delete (optional)
     * @param string $disk Storage disk to use (default: 'public')
     * @return string|null The path of the uploaded file or null if upload fails
     */
    function uploadFile(UploadedFile $file, string $path, ?string $oldFile = null, string $disk = 'public'): ?string
    {
        try {
            // Delete old file if exists
            if ($oldFile) {
                Storage::disk($disk)->delete($oldFile);
            }

            // Generate unique filename
            $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();

            // Store the file
            $filePath = $file->storeAs($path, $filename, $disk);

            return $filePath;
        } catch (\Exception $e) {
            Log::error('File upload failed: ' . $e->getMessage());
            return null;
        }
    }
}

if (!function_exists('deleteFile')) {
    /**
     * Delete a file from storage
     *
     * @param string|null $path The file path to delete
     * @param string $disk Storage disk to use (default: 'public')
     * @return bool Whether the deletion was successful
     */
    function deleteFile(?string $path, string $disk = 'public'): bool
    {
        if (!$path) {
            return false;
        }

        try {
            return Storage::disk($disk)->delete($path);
        } catch (\Exception $e) {
            Log::error('File deletion failed: ' . $e->getMessage());
            return false;
        }
    }
}

if (!function_exists('uploadImage')) {
    /**
     * Upload an image with optional resizing
     *
     * @param UploadedFile $file The uploaded image file
     * @param string $path The storage path
     * @param array $dimensions Array of width/height pairs for resizing (e.g., ['width' => 300, 'height' => 300])
     * @param string|null $oldFile Path of old file to delete (optional)
     * @param string $disk Storage disk to use (default: 'public')
     * @return string|null The path of the uploaded image or null if upload fails
     */
    function uploadImage(UploadedFile $file, string $path, array $dimensions = [], ?string $oldFile = null, string $disk = 'public'): ?string
    {
        try {
            // Check if file is an image
            if (!$file->isValid() || !Str::startsWith($file->getMimeType(), 'image/')) {
                throw new \Exception('Invalid image file');
            }

            // Delete old file if exists
            if ($oldFile) {
                Storage::disk($disk)->delete($oldFile);
            }

            // Generate unique filename
            $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();

            // Create image instance
            $image = Image::make($file);

            // Resize if dimensions provided
            if (!empty($dimensions)) {
                $image->resize($dimensions['width'] ?? null, $dimensions['height'] ?? null, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
            }

            // Store the image
            $filePath = $path . '/' . $filename;
            Storage::disk($disk)->put($filePath, $image->encode());

            return $filePath;
        } catch (\Exception $e) {
            Log::error('Image upload failed: ' . $e->getMessage());
            return null;
        }
    }
}
