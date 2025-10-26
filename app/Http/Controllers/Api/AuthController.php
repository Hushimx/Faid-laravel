<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Support\ApiResponse;
use Illuminate\Http\Request;
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
        return ApiResponse::success(
            new UserResource($request->user()),
            'User data retrieved successfully'
        );
    }

    /**
     * Update authenticated user's profile
     */
    public function update(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => ['sometimes', 'required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:255'],
            'profile_picture' => ['nullable', 'image'],
            'current_password' => ['required_with:new_password', 'nullable', 'string'],
            'new_password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

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

        // Remove unwanted fields from array
        unset($validated['current_password'], $validated['new_password']);

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

        $user->update($validated);

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
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:255'],
            'profile_picture' => ['nullable', 'image'],
        ]);

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

        // Hash password before creating
        $validated['password'] = Hash::make($validated['password']);

        try {
            $user = User::create($validated);

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
