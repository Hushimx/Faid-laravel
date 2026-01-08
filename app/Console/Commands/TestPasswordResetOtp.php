<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class TestPasswordResetOtp extends Command
{
    protected $signature = 'test:password-reset-otp {phone=966596000912}';
    protected $description = 'Test password reset OTP flow with a phone number';

    public function handle()
    {
        $testPhone = $this->argument('phone');
        $phoneFormatted = ltrim($testPhone, '+');

        $this->info("=== Testing Password Reset OTP Flow ===");
        $this->info("Phone: {$phoneFormatted}\n");

        // Step 1: Check if user exists, create if not
        $this->info("Step 1: Checking/Creating user...");
        $user = User::where('phone', $phoneFormatted)->first();

        if (!$user) {
            $this->warn("User not found. Creating test user...");
            $user = User::create([
                'first_name' => 'Test',
                'last_name' => 'User',
                'email' => 'test' . time() . '@example.com',
                'phone' => $phoneFormatted,
                'password' => bcrypt('password123'),
                'type' => 'user',
                'status' => 'active',
            ]);
            $this->info("✓ User created with ID: {$user->id}");
        } else {
            $this->info("✓ User found with ID: {$user->id}");
        }

        // Step 2: Send initial OTP (simulating sendOtp endpoint)
        $this->info("\nStep 2: Sending initial OTP via WhatsApp...");
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
                $this->info("✓ Initial OTP sent successfully via WhatsApp");
                $this->line("   OTP Code: <fg=cyan>{$otp}</>");
            } else {
                $this->error("✗ Failed to send initial OTP");
                $this->line("   Response: " . json_encode($json_response));
                $this->line("   OTP saved in DB: <fg=cyan>{$otp}</>");
            }
        } catch (\Exception $e) {
            $this->error("✗ Exception sending initial OTP: {$e->getMessage()}");
            $this->line("   OTP saved in DB: <fg=cyan>{$otp}</>");
        }

        // Step 3: Simulate verifyOtp with password_reset type
        $this->info("\nStep 3: Verifying OTP and generating password reset OTP...");
        $this->line("   Verifying with OTP: <fg=cyan>{$otp}</>");

        if ($user->fresh()->otp === $otp) {
            $this->info("✓ OTP verified");
            
            // Simulate what verifyOtp does for password_reset
            $resetOtp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
            $user->otp = $resetOtp;
            $user->otp_expires_at = now()->addMinutes(10);
            $user->save();
            
            $this->info("✓ New password reset OTP generated: <fg=cyan>{$resetOtp}</>");
            
            // Send the password reset OTP (this is what we fixed)
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
                    $this->info("✓ Password reset OTP sent successfully via WhatsApp");
                    $this->line("   Password Reset OTP Code: <fg=green>{$resetOtp}</>");
                } else {
                    $this->error("✗ Failed to send password reset OTP");
                    $this->line("   Response: " . json_encode($resetJsonResponse));
                    $this->line("   Password Reset OTP saved in DB: <fg=cyan>{$resetOtp}</>");
                }
            } catch (\Exception $e) {
                $this->error("✗ Exception sending password reset OTP: {$e->getMessage()}");
                $this->line("   Password Reset OTP saved in DB: <fg=cyan>{$resetOtp}</>");
            }
        } else {
            $this->error("✗ OTP verification failed");
        }

        $this->info("\n=== Test Complete ===");
        $this->line("Phone: {$phoneFormatted}");
        $this->line("User ID: {$user->id}");
        $this->line("Current OTP in DB: <fg=cyan>" . $user->fresh()->otp . "</>");

        return 0;
    }
}

