<?php

/**
 * Test script for password reset OTP flow
 * Run this via: php artisan tinker < test_password_reset.php
 * Or copy-paste into tinker directly
 */

use App\Models\User;
use Illuminate\Support\Facades\Http;

$testPhone = '966596000912';
$phoneFormatted = ltrim($testPhone, '+');

echo "=== Testing Password Reset OTP Flow ===\n\n";

// Step 1: Check if user exists, create if not
echo "Step 1: Checking/Creating user with phone: {$phoneFormatted}\n";
$user = User::where('phone', $phoneFormatted)->first();

if (!$user) {
    echo "User not found. Creating test user...\n";
    $user = User::create([
        'first_name' => 'Test',
        'last_name' => 'User',
        'email' => 'test' . time() . '@example.com',
        'phone' => $phoneFormatted,
        'password' => bcrypt('password123'),
        'type' => 'user',
        'status' => 'active',
    ]);
    echo "User created with ID: {$user->id}\n";
} else {
    echo "User found with ID: {$user->id}\n";
}

// Step 2: Send initial OTP (like when user clicks "Forgot Password")
echo "\nStep 2: Sending initial OTP via sendOtp endpoint...\n";
$otp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
$user->otp = $otp;
$user->otp_expires_at = now()->addMinutes(10);
$user->save();

$data = [
    'number' => $phoneFormatted,
    'type' => 'text',
    'message' => 'رمز التحقق الخاص بك هو: ' . $otp,
    'instance_id' => config('services.whatsapp.instance_id'),
    'access_token' => config('services.whatsapp.access_token'),
];

try {
    $response = Http::timeout(10)->post(config('services.whatsapp.api_url'), $data);
    $json_response = $response->json();
    
    if (isset($json_response['status']) && $json_response['status'] == 'success') {
        echo "✓ Initial OTP sent successfully via WhatsApp\n";
        echo "OTP Code: {$otp}\n";
    } else {
        echo "✗ Failed to send initial OTP. Response: " . json_encode($json_response) . "\n";
    }
} catch (\Exception $e) {
    echo "✗ Exception sending initial OTP: {$e->getMessage()}\n";
    echo "OTP saved in DB: {$otp}\n";
}

// Step 3: Simulate verifyOtp call with password_reset type
echo "\nStep 3: Verifying OTP and generating password reset OTP...\n";
echo "Using OTP: {$otp}\n";

// This simulates what happens in verifyOtp when type is password_reset
if ($user->otp === $otp) {
    echo "✓ OTP verified\n";
    
    $resetOtp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
    $user->otp = $resetOtp;
    $user->otp_expires_at = now()->addMinutes(10);
    $user->save();
    
    echo "New password reset OTP generated: {$resetOtp}\n";
    
    // Send the password reset OTP
    $resetData = [
        'number' => $phoneFormatted,
        'type' => 'text',
        'message' => 'رمز التحقق لإعادة تعيين كلمة المرور هو: ' . $resetOtp,
        'instance_id' => config('services.whatsapp.instance_id'),
        'access_token' => config('services.whatsapp.access_token'),
    ];
    
    try {
        $resetResponse = Http::timeout(10)->post(config('services.whatsapp.api_url'), $resetData);
        $resetJsonResponse = $resetResponse->json();
        
        if (isset($resetJsonResponse['status']) && $resetJsonResponse['status'] == 'success') {
            echo "✓ Password reset OTP sent successfully via WhatsApp\n";
            echo "Password Reset OTP Code: {$resetOtp}\n";
        } else {
            echo "✗ Failed to send password reset OTP. Response: " . json_encode($resetJsonResponse) . "\n";
            echo "Password Reset OTP saved in DB: {$resetOtp}\n";
        }
    } catch (\Exception $e) {
        echo "✗ Exception sending password reset OTP: {$e->getMessage()}\n";
        echo "Password Reset OTP saved in DB: {$resetOtp}\n";
    }
} else {
    echo "✗ OTP verification failed\n";
}

echo "\n=== Test Complete ===\n";
echo "Phone: {$phoneFormatted}\n";
echo "User ID: {$user->id}\n";
echo "Current OTP in DB: {$user->fresh()->otp}\n";

