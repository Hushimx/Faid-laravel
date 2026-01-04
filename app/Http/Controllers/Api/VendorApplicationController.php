<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\VendorApplication;
use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class VendorApplicationController extends Controller
{
    /**
     * Submit a new vendor application
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        // Only users can apply (not vendors or admins)
        if ($user->type !== 'user') {
            return ApiResponse::error(
                'Only regular users can apply to become vendors',
                [],
                403
            );
        }

        // Check if user already has an application
        $existingApplication = VendorApplication::where('user_id', $user->id)->first();
        if ($existingApplication) {
            return ApiResponse::error(
                'You have already submitted an application',
                [],
                400
            );
        }

        $validated = $request->validate([
            'country_id' => ['nullable', 'exists:countries,id'],
            'city_id' => ['nullable', 'exists:cities,id'],
            'lat' => ['nullable', 'numeric', 'between:-90,90'],
            'lng' => ['nullable', 'numeric', 'between:-180,180'],
            'banner' => ['nullable', 'image'],
            'bio' => ['nullable', 'string', 'max:1000'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'custom_category' => ['nullable', 'string', 'max:255', 'required_without:category_id'],
            'meta' => ['nullable', 'array'],
        ]);

        $applicationData = $validated;

        // Handle banner upload
        if ($request->hasFile('banner')) {
            $bannerPath = uploadImage(
                $request->file('banner'),
                'vendor-banners'
            );

            if (!$bannerPath) {
                return ApiResponse::error('Failed to upload vendor banner', [], 500);
            }

            $applicationData['banner'] = $bannerPath;
        }

        // Handle meta if it's a string
        if (isset($applicationData['meta']) && is_string($applicationData['meta'])) {
            $decodedMeta = json_decode($applicationData['meta'], true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $applicationData['meta'] = $decodedMeta;
            }
        }

        $applicationData['user_id'] = $user->id;
        $applicationData['status'] = 'pending';

        try {
            $application = VendorApplication::create($applicationData);
            $application->loadMissing(['country', 'city', 'category']);

            return ApiResponse::success(
                $application,
                'Vendor application submitted successfully'
            );
        } catch (\Exception $e) {
            return ApiResponse::error(
                'Failed to submit application',
                [],
                500
            );
        }
    }

    /**
     * Get current user's application status
     */
    public function show()
    {
        $user = Auth::user();

        $application = VendorApplication::where('user_id', $user->id)
            ->with(['country', 'city', 'category', 'reviewer'])
            ->first();

        if (!$application) {
            return ApiResponse::error(
                'No application found',
                [],
                404
            );
        }

        return ApiResponse::success($application, 'Application retrieved successfully');
    }
}


