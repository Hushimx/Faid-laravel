<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Mail\SendOtp;
use App\Models\FcmToken;
use App\Models\User;
use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Cache;
use Propaganistas\LaravelPhone\Rules\Phone;

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
            'fcm_token' => ['nullable', 'string'],
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

        // Save FCM token if provided
        if ($request->filled('fcm_token')) {
            // Search globally since token has unique constraint
            FcmToken::updateOrCreate(
                ['token' => $request->fcm_token],
                [
                    'user_id' => $user->id,
                    'device_type' => 'mobile',
                    'device_name' => $deviceName,
                    'is_active' => true,
                    'last_used_at' => now(),
                ]
            );
        }

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
            'first_name' => ['sometimes', 'required', 'string', 'max:255'],
            'last_name' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => ['sometimes', 'required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:255'],
            'profile_picture' => ['nullable', 'image'],
            'current_password' => ['required_with:new_password', 'nullable', 'string'],
            'new_password' => ['nullable', 'string', 'min:8', 'confirmed', 'regex:/^[a-zA-Z0-9]+$/'],
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
            try {
                $path = uploadImage(
                    $request->file('profile_picture'),
                    'profile-pictures',
                    ['width' => 300, 'height' => 300],
                    $user->profile_picture
                );

                if (!$path) {
                    Log::error('Profile picture upload returned null', [
                        'file_name' => $request->file('profile_picture')->getClientOriginalName(),
                        'mime_type' => $request->file('profile_picture')->getMimeType(),
                        'size' => $request->file('profile_picture')->getSize(),
                    ]);
                    return ApiResponse::error(
                        'Failed to upload profile picture',
                        [
                            'profile_picture' => ['An error occurred while uploading the image.'],
                        ],
                        500
                    );
                }

                $validated['profile_picture'] = $path;
            } catch (\Exception $e) {
                Log::error('Profile picture upload exception: ' . $e->getMessage(), [
                    'exception' => $e,
                ]);
                return ApiResponse::error(
                    'Failed to upload profile picture',
                    [
                        'profile_picture' => ['An error occurred while uploading the image.'],
                    ],
                    500
                );
            }
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

        // Delete FCM token if provided
        if ($request->filled('fcm_token')) {
            $user->fcmTokens()->where('token', $request->fcm_token)->delete();
        }

        // Revoke all tokens
        $user->tokens()->delete();
        return ApiResponse::success(null, 'Logged out successfully');
    }

    /**
     * Delete the authenticated user's account.
     */
    public function deleteAccount(Request $request)
    {
        $user = $request->user();

        // Delete FCM token if provided
        if ($request->filled('fcm_token')) {
            $user->fcmTokens()->where('token', $request->fcm_token)->delete();
        }

        // Revoke all tokens
        $user->tokens()->delete();

        // Delete the user (this will cascade delete related records based on database constraints)
        $user->delete();

        return ApiResponse::success(null, 'Account deleted successfully');
    }

    /**
     * Register a new user (stores data temporarily until OTP verification)
     */
    public function register(Request $request)
    {
        $rules = [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20', Rule::when($request->filled('phone'), [new Phone])],
            'password' => ['required', 'string', 'min:8', 'confirmed', 'regex:/^[a-zA-Z0-9]+$/'],
            'type' => ['nullable', 'string', Rule::in(['user', 'vendor'])],
            'fcm_token' => ['nullable', 'string'],
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

        // Normalize phone number (remove leading +); allow null for email-only OTP flow
        $phone = isset($validated['phone']) && $validated['phone'] !== '' && $validated['phone'] !== null
            ? ltrim($validated['phone'], '+')
            : null;
        $validated['phone'] = $phone;

        // Check if email or phone already exists in database
        $emailExists = User::where('email', $validated['email'])->exists();
        if ($emailExists) {
            return ApiResponse::error('Email already taken', [
                'email' => ['The email has already been taken.']
            ], 422);
        }

        if ($phone !== null) {
            $phoneExists = User::where('phone', $phone)->exists();
            if ($phoneExists) {
                return ApiResponse::error('Phone number already taken', [
                    'phone' => ['The phone number has already been taken.']
                ], 422);
            }
        }

        $vendorProfileData = $validated['vendor_profile'] ?? [];

        if (isset($vendorProfileData['meta']) && is_string($vendorProfileData['meta'])) {
            $decodedMeta = json_decode($vendorProfileData['meta'], true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $vendorProfileData['meta'] = $decodedMeta;
            }
        }

        unset($validated['vendor_profile']);

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
        $validated['status'] = $validated['status'] ?? 'active';

        // Hash password before storing
        $validated['password'] = Hash::make($validated['password']);

        // Store registration data in cache temporarily (expires in 15 minutes)
        // Key by email so sendOtp/verifyOtp can find pending registration by email
        $emailNormalized = strtolower($validated['email']);
        $cacheKey = 'pending_registration_' . $emailNormalized;
        $registrationData = [
            'user_data' => $validated,
            'vendor_profile' => $vendorProfileData,
            'fcm_token' => $request->fcm_token,
            'device_name' => $request->userAgent() ?: 'unknown',
            'created_at' => now()->toIso8601String(),
        ];

        Cache::put($cacheKey, $registrationData, now()->addMinutes(15));

        // Return success immediately - OTP will be sent by the frontend via sendOtp endpoint
        return ApiResponse::success(
            [
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'message' => 'Registration data stored. Please verify OTP to complete registration.'
            ],
            'Registration initiated. Please verify OTP.',
            200
        );
    }

    public function sendOtp(Request $request)
    {
        $request->validate([
            'email' => ['required', 'string', 'email', 'max:255'],
        ]);

        $email = strtolower($request->email);

        // Check if user exists
        $user = User::where('email', $email)->first();

        // Check if there's a pending registration (keyed by email)
        $cacheKey = 'pending_registration_' . $email;
        $pendingRegistration = Cache::get($cacheKey);

        // If neither user exists nor pending registration, return error
        if (!$user && !$pendingRegistration) {
            return ApiResponse::error('Email not found. Please register first.', [], 404);
        }

        $otp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);

        if ($user) {
            // Existing user - update OTP in database
            $user->otp = $otp;
            $user->otp_expires_at = now()->addMinutes(10);
            $user->save();
        } else {
            // Pending registration - store OTP in cache
            $pendingRegistration['otp'] = $otp;
            $pendingRegistration['otp_expires_at'] = now()->addMinutes(10)->toIso8601String();
            Cache::put($cacheKey, $pendingRegistration, now()->addMinutes(15));
        }

        // Build User-like object for SendOtp mailable (existing user or dummy for pending)
        $userForMail = $user;
        if (!$userForMail && $pendingRegistration) {
            $userData = $pendingRegistration['user_data'] ?? [];
            $userForMail = new User();
            $userForMail->email = $userData['email'] ?? $email;
            $userForMail->first_name = $userData['first_name'] ?? '';
            $userForMail->last_name = $userData['last_name'] ?? null;
        }

        try {
            Mail::to($email)->send(new SendOtp($userForMail, $otp));
        } catch (\Exception $e) {
            report($e);
            if ($user || $pendingRegistration) {
                return ApiResponse::success([], 'OTP generated. If you did not receive it, please request a new one.');
            }
            return ApiResponse::error('Failed to send OTP', [], 500);
        }

        return ApiResponse::success([], 'OTP sent successfully');
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => ['required', 'string', 'email', 'max:255'],
            'otp' => ['required', 'string', 'max:6'],
            'type' => 'required|string|in:verification,password_reset',
        ]);

        $email = strtolower($request->email);
        $user = User::where('email', $email)->first();

        // Check for pending registration (keyed by email)
        $cacheKey = 'pending_registration_' . $email;
        $pendingRegistration = Cache::get($cacheKey);

        // Handle pending registration (new user registration)
        if (!$user && $pendingRegistration) {
            // Verify OTP from cache
            if (!isset($pendingRegistration['otp']) || $pendingRegistration['otp'] !== $request->otp) {
                return ApiResponse::error('Invalid OTP', [], 400);
            }

            $otpExpiresAt = isset($pendingRegistration['otp_expires_at']) 
                ? \Carbon\Carbon::parse($pendingRegistration['otp_expires_at']) 
                : null;

            if ($otpExpiresAt && $otpExpiresAt < now()) {
                return ApiResponse::error('OTP expired', [], 400);
            }

            if ($request->type === 'verification') {
                // Create the user account
                try {
                    $user = DB::transaction(function () use ($pendingRegistration) {
                        $userData = $pendingRegistration['user_data'];
                        $vendorProfileData = $pendingRegistration['vendor_profile'] ?? [];
                        $type = $userData['type'] ?? 'user';

                        $user = User::create($userData);

                        if ($type === 'vendor' && !empty($vendorProfileData)) {
                            $user->vendorProfile()->updateOrCreate([], $vendorProfileData);
                        }

                        return $user;
                    });

                    $user->loadMissing(['vendorProfile.country', 'vendorProfile.city']);

                    // Create token
                    $deviceName = $pendingRegistration['device_name'] ?? 'unknown';
                    $token = $user->createToken($deviceName)->plainTextToken;

                    // Save FCM token if provided
                    if (!empty($pendingRegistration['fcm_token'])) {
                        FcmToken::updateOrCreate(
                            ['token' => $pendingRegistration['fcm_token']],
                            [
                                'user_id' => $user->id,
                                'device_type' => 'mobile',
                                'device_name' => $deviceName,
                                'is_active' => true,
                                'last_used_at' => now(),
                            ]
                        );
                    }

                    // Clear pending registration from cache
                    Cache::forget($cacheKey);

                    // Mark email as verified
                    $user->email_verified_at = now();
                    $user->save();

                    return ApiResponse::success([
                        'token' => $token,
                        'user' => new UserResource($user)
                    ], 'OTP verified successfully. Account created.');
                } catch (\Exception $e) {
                    report($e);
                    return ApiResponse::error('Failed to create account', [], 500);
                }
            } else {
                return ApiResponse::error('Invalid verification type for pending registration', [], 400);
            }
        }

        // Handle existing user
        if (!$user) {
            return ApiResponse::error('User not found', [], 404);
        }

        if ($user->otp !== $request->otp) {
            return ApiResponse::error('Invalid OTP', [], 400);
        }

        if ($user->otp_expires_at < now()) {
            return ApiResponse::error('OTP expired', [], 400);
        }

        if ($request->type === 'verification') {
            $user->otp = null;
            $user->otp_expires_at = null;
            $user->email_verified_at = now();
        }
        if ($request->type === 'password_reset') {
            $otp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
            $user->otp = $otp;
            $user->otp_expires_at = now()->addMinutes(10);
            $user->save();

            try {
                Mail::to($user->email)->send(new SendOtp($user, $otp));
            } catch (\Exception $e) {
                report($e);
            }

            return ApiResponse::success([
                'otp' => $user->otp,
            ], 'OTP verified successfully. Password reset OTP has been sent.');
        }

        $user->save();

        return ApiResponse::success([
            'otp' => null,
        ], 'OTP verified successfully');
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:8', 'confirmed', 'regex:/^[a-zA-Z0-9]+$/'],
            'otp' => ['required', 'string', 'size:6'],
        ]);

        $email = strtolower($request->email);

        // Check if user exists by email
        $user = User::where('email', $email)->first();

        if (!$user) {
            return ApiResponse::error('User not found', [], 404);
        }

        if ($user->otp !== $request->otp) {
            return ApiResponse::error('Invalid OTP', [], 400);
        }

        if ($user->otp_expires_at < now()) {
            return ApiResponse::error('OTP expired', [], 400);
        }

        $user->otp = null;
        $user->otp_expires_at = null;
        $user->password = Hash::make($request->password);
        $user->save();

        return ApiResponse::success([], 'Password reset successfully');
    }
}
