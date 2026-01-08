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
            'business_name' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'bio' => ['required', 'string', 'max:1000'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'custom_category' => ['nullable', 'string', 'max:255'],
        ]);

        // Ensure either category_id or custom_category is provided
        if (empty($validated['category_id']) && empty($validated['custom_category'])) {
            return ApiResponse::error(
                'Either category or custom category must be provided',
                ['category_id' => ['Either category or custom category must be provided']],
                422
            );
        }

        $applicationData = $validated;
        $applicationData['user_id'] = $user->id;
        $applicationData['status'] = 'pending';

        try {
            $application = VendorApplication::create($applicationData);
            $application->loadMissing(['category']);

            return ApiResponse::success(
                $application,
                'Vendor application submitted successfully'
            );
        } catch (\Exception $e) {
            Log::error('Vendor application submission error: ' . $e->getMessage(), [
                'exception' => $e,
                'user_id' => $user->id,
                'data' => $applicationData,
            ]);
            return ApiResponse::error(
                'Failed to submit application: ' . $e->getMessage(),
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
            ->with(['category', 'reviewer'])
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


