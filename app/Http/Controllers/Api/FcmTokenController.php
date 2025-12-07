<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterFcmTokenRequest;
use App\Models\FcmToken;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FcmTokenController extends Controller
{
    /**
     * Register or update FCM token for authenticated user.
     */
    public function register(RegisterFcmTokenRequest $request): JsonResponse
    {
        try {
            $user = $request->user();

            // Check if token already exists
            $fcmToken = FcmToken::where('token', $request->token)->first();

            if ($fcmToken) {
                // Update existing token
                $fcmToken->update([
                    'user_id' => $user->id,
                    'device_type' => $request->device_type,
                    'device_name' => $request->device_name,
                    'is_active' => true,
                    'last_used_at' => now(),
                ]);
            } else {
                // Create new token
                $fcmToken = FcmToken::create([
                    'user_id' => $user->id,
                    'token' => $request->token,
                    'device_type' => $request->device_type,
                    'device_name' => $request->device_name,
                    'is_active' => true,
                    'last_used_at' => now(),
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'FCM token registered successfully',
                'data' => $fcmToken
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to register FCM token',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete FCM token (on logout).
     */
    public function delete(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'token' => 'required|string'
            ]);

            $deleted = FcmToken::where('token', $request->token)
                ->where('user_id', $request->user()->id)
                ->delete();

            if ($deleted) {
                return response()->json([
                    'success' => true,
                    'message' => 'FCM token deleted successfully'
                ], 200);
            }

            return response()->json([
                'success' => false,
                'message' => 'FCM token not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete FCM token',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all FCM tokens for authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $tokens = FcmToken::where('user_id', $request->user()->id)
                ->where('is_active', true)
                ->get();

            return response()->json([
                'success' => true,
                'data' => $tokens
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve FCM tokens',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
