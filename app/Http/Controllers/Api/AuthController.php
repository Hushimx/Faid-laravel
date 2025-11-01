<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    /**
     * Login and issue token
     */
    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            return ApiResponse::error(
                'Invalid credentials',
                [],
                401
            );
        }

        if ($user->status !== 'active') {
            return ApiResponse::error(
                'Account is inactive',
                [],
                403
            );
        }

        $user->loadMissing(['vendorProfile.country', 'vendorProfile.city']);

        // Create token
        $deviceName = $request->userAgent() ?: 'unknown';
        $token = $user->createToken($deviceName)->plainTextToken;

        return ApiResponse::success(
            [
                'token' => $token,
                'user' => new UserResource($user),
            ],
            'Logged in successfully',
        );
    }


    /**
     * Get authenticated user data
     */
    public function me(Request $request)
    {
        $user = $request->user()->loadMissing(['vendorProfile.country', 'vendorProfile.city']);

        return ApiResponse::success(
            new UserResource($user),
            'User data retrieved successfully'
        );
    }

    /**
     * Update authenticated user's profile
     */
    public function update(Request $request)
    {
        $user = $request->user();

        $rules = [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => ['sometimes', 'required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:255'],
            'profile_picture' => ['nullable', 'image'],
            'current_password' => ['required_with:new_password', 'nullable', 'string'],
            'new_password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ];

        $shouldHandleVendor = $user->type === 'vendor';

        if ($shouldHandleVendor) {
            $rules = array_merge($rules, [
                'vendor_profile.country_id' => ['nullable', 'exists:countries,id'],
                'vendor_profile.city_id' => ['nullable', 'exists:cities,id'],
                'vendor_profile.lat' => ['nullable', 'numeric', 'between:-90,90'],
                'vendor_profile.lng' => ['nullable', 'numeric', 'between:-180,180'],
                'vendor_profile.banner' => ['nullable', 'image'],
                'vendor_profile.bio' => ['nullable', 'string', 'max:1000'],
                'vendor_profile.meta' => ['nullable', 'array'],
            ]);
        }

        $validated = $request->validate($rules);

        // Handle password change
        if (isset($validated['current_password'])) {
            if (!Hash::check($validated['current_password'], $user->password)) {
                return ApiResponse::error(
                    'Current password is incorrect',
                    [
                        'current_password' => ['The current password is incorrect.'],
                    ],
                    422
                );
            }
            $validated['password'] = Hash::make($validated['new_password']);
        }

        // Extract vendor profile data if provided
        $vendorProfileData = $validated['vendor_profile'] ?? [];

        if (isset($vendorProfileData['meta']) && is_string($vendorProfileData['meta'])) {
            $decodedMeta = json_decode($vendorProfileData['meta'], true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $vendorProfileData['meta'] = $decodedMeta;
            }
        }

        // Remove unwanted fields from array
        unset($validated['current_password'], $validated['new_password'], $validated['vendor_profile']);

        // Handle profile picture upload
        if ($request->hasFile('profile_picture')) {
            $path = uploadImage(
                $request->file('profile_picture'),
                'profile-pictures',
                ['width' => 300, 'height' => 300],
                $user->profile_picture
            );

            if (!$path) {
                return ApiResponse::error(
                    'Failed to upload profile picture',
                    [
                        'profile_picture' => ['An error occurred while uploading the image.'],
                    ],
                    500
                );
            }

            $validated['profile_picture'] = $path;
        }

        if ($shouldHandleVendor && $request->hasFile('vendor_profile.banner')) {
            $bannerPath = uploadImage(
                $request->file('vendor_profile.banner'),
                'vendor-banners',
                [],
                $user->vendorProfile?->banner
            );

            if (!$bannerPath) {
                return ApiResponse::error(
                    'Failed to upload vendor banner',
                    [
                        'vendor_profile.banner' => ['An error occurred while uploading the vendor banner.'],
                    ],
                    500
                );
            }

            $vendorProfileData['banner'] = $bannerPath;
        }

        $user->update($validated);

        $shouldUpdateVendorProfile = $shouldHandleVendor && count($vendorProfileData) > 0;

        if ($shouldUpdateVendorProfile) {
            $user->vendorProfile()->updateOrCreate([], $vendorProfileData);
        }

        $user->loadMissing(['vendorProfile.country', 'vendorProfile.city']);

        return ApiResponse::success(
            new UserResource($user),
            'Profile updated successfully'
        );
    }

    /**
     * Logout the authenticated user.
     *
     * If request contains `all=true`, revoke all tokens for the user.
     * Otherwise revoke the current access token.
     */
    public function logout(Request $request)
    {
        $user = $request->user();

        // Revoke all tokens
        $user->tokens()->delete();
        return ApiResponse::success(null, 'Logged out successfully');
    }

    /**
     * Register a new user
     */
    public function register(Request $request)
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:255'],
            'profile_picture' => ['nullable', 'image'],
            'type' => ['nullable', 'string', Rule::in(['user', 'vendor'])],
        ];

        $type = $request->input('type', 'user');

        if ($type === 'vendor') {
            $rules = array_merge($rules, [
                'vendor_profile.country_id' => ['nullable', 'exists:countries,id'],
                'vendor_profile.city_id' => ['nullable', 'exists:cities,id'],
                'vendor_profile.lat' => ['nullable', 'numeric', 'between:-90,90'],
                'vendor_profile.lng' => ['nullable', 'numeric', 'between:-180,180'],
                'vendor_profile.banner' => ['nullable', 'image'],
                'vendor_profile.bio' => ['nullable', 'string', 'max:1000'],
                'vendor_profile.meta' => ['nullable', 'array'],
            ]);
        }

        $validated = $request->validate($rules);

        $vendorProfileData = $validated['vendor_profile'] ?? [];

        if (isset($vendorProfileData['meta']) && is_string($vendorProfileData['meta'])) {
            $decodedMeta = json_decode($vendorProfileData['meta'], true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $vendorProfileData['meta'] = $decodedMeta;
            }
        }

        unset($validated['vendor_profile']);

        // Handle optional profile picture upload
        if ($request->hasFile('profile_picture')) {
            $path = uploadImage(
                $request->file('profile_picture'),
                'profile-pictures',
                ['width' => 300, 'height' => 300]
            );

            if (!$path) {
                return ApiResponse::error('Failed to upload profile picture', [], 500);
            }

            $validated['profile_picture'] = $path;
        }

        if ($type === 'vendor' && $request->hasFile('vendor_profile.banner')) {
            $bannerPath = uploadImage(
                $request->file('vendor_profile.banner'),
                'vendor-banners'
            );

            if (!$bannerPath) {
                return ApiResponse::error('Failed to upload vendor banner', [], 500);
            }

            $vendorProfileData['banner'] = $bannerPath;
        }

        $validated['type'] = $validated['type'] ?? $type ?? 'user';
        $type = $validated['type'];

        // Hash password before creating
        $validated['password'] = Hash::make($validated['password']);

        try {
            $user = DB::transaction(function () use ($validated, $vendorProfileData, $type) {
                $user = User::create($validated);

                if ($type === 'vendor') {
                    $user->vendorProfile()->updateOrCreate([], $vendorProfileData);
                }

                return $user;
            });

            $user->loadMissing(['vendorProfile.country', 'vendorProfile.city']);

            // Return created user with transformed data
            return ApiResponse::success(
                new UserResource($user),
                'Account created',
                201
            );
        } catch (\Exception $e) {
            report($e);
            return ApiResponse::error('Failed to create account', [], 500);
        }
    }
}
