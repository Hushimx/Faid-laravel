<?php

/**
 * Test script for reset password API endpoint
 * This tests the actual API endpoint with the phone number format that the frontend sends
 */

use App\Models\User;
use Illuminate\Support\Facades\Http;

$testPhone = '966596000912';
$phoneWithPlus = '+' . $testPhone; // This is what frontend sends

echo "=== Testing Reset Password API Endpoint ===\n\n";

// Step 1: Ensure user exists and has a valid OTP
echo "Step 1: Setting up user with valid OTP...\n";
$user = User::where('phone', $testPhone)->first();

if (!$user) {
    echo "User not found. Creating test user...\n";
    $user = User::create([
        'first_name' => 'Test',
        'last_name' => 'User',
        'email' => 'test' . time() . '@example.com',
        'phone' => $testPhone,
        'password' => bcrypt('oldpassword123'),
        'type' => 'user',
        'status' => 'active',
    ]);
    echo "User created with ID: {$user->id}\n";
} else {
    echo "User found with ID: {$user->id}\n";
}

// Set a test OTP
$testOtp = '123456';
$user->otp = $testOtp;
$user->otp_expires_at = now()->addMinutes(10);
$user->save();

echo "OTP set: {$testOtp}\n";

// Step 2: Test the reset password endpoint
echo "\nStep 2: Testing reset password API with phone format: {$phoneWithPlus}\n";

$apiUrl = config('app.url') . '/api/reset-password';
$payload = [
    'phone' => $phoneWithPlus, // This is what frontend sends
    'password' => 'newpassword123',
    'password_confirmation' => 'newpassword123',
    'otp' => $testOtp,
];

echo "Making API request...\n";
echo "URL: {$apiUrl}\n";
echo "Payload: " . json_encode($payload, JSON_PRETTY_PRINT) . "\n\n";

try {
    $response = Http::post($apiUrl, $payload);
    $statusCode = $response->status();
    $responseData = $response->json();
    
    echo "Response Status: {$statusCode}\n";
    echo "Response: " . json_encode($responseData, JSON_PRETTY_PRINT) . "\n\n";
    
    if ($statusCode === 200 && isset($responseData['success']) && $responseData['success']) {
        echo "✓ Password reset successful!\n";
        
        // Verify password was changed
        $user->refresh();
        if (\Hash::check('newpassword123', $user->password)) {
            echo "✓ Password was successfully updated in database\n";
        } else {
            echo "✗ Password was NOT updated in database\n";
        }
        
        if ($user->otp === null) {
            echo "✓ OTP was cleared from database\n";
        } else {
            echo "✗ OTP was NOT cleared from database\n";
        }
    } else {
        echo "✗ Password reset failed\n";
        if (isset($responseData['message'])) {
            echo "Error: {$responseData['message']}\n";
        }
        if (isset($responseData['errors'])) {
            echo "Validation Errors: " . json_encode($responseData['errors'], JSON_PRETTY_PRINT) . "\n";
        }
    }
} catch (\Exception $e) {
    echo "✗ Exception: {$e->getMessage()}\n";
}

echo "\n=== Test Complete ===\n";


