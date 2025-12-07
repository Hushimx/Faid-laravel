<?php

namespace App\Services;

use App\Models\FcmToken;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification as FCMNotification;
use Kreait\Firebase\Messaging;
use Illuminate\Support\Facades\Log;

class FirebaseService
{
    protected Messaging $messaging;

    public function __construct()
    {
        $this->initializeFirebase();
    }

    /**
     * Initialize Firebase with service account.
     */
    protected function initializeFirebase(): void
    {
        try {
            $credentialsPath = config('firebase.credentials');

            if (!file_exists($credentialsPath)) {
                throw new \Exception("Firebase credentials file not found at: {$credentialsPath}");
            }

            $factory = (new Factory)->withServiceAccount($credentialsPath);
            $this->messaging = $factory->createMessaging();
        } catch (\Exception $e) {
            Log::error('Firebase initialization failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Send notification to a single token.
     *
     * @param string $token FCM device token
     * @param string $title Notification title
     * @param string $body Notification body
     * @param array $data Additional data payload
     * @return bool Success status
     */
    public function sendToToken(string $token, string $title, string $body, array $data = []): bool
    {
        try {
            $notification = FCMNotification::create($title, $body);

            $message = CloudMessage::withTarget('token', $token)
                ->withNotification($notification)
                ->withData($data);

            $this->messaging->send($message);

            return true;
        } catch (\Exception $e) {
            Log::error('FCM send to token failed: ' . $e->getMessage(), [
                'token' => $token,
                'error' => $e->getMessage()
            ]);

            // Mark token as inactive if it's invalid
            if ($this->isInvalidTokenError($e)) {
                $this->markTokenAsInactive($token);
            }

            return false;
        }
    }

    /**
     * Send notification to multiple tokens (batch).
     *
     * @param array $tokens Array of FCM device tokens
     * @param string $title Notification title
     * @param string $body Notification body
     * @param array $data Additional data payload
     * @return array ['success' => int, 'failure' => int, 'invalid_tokens' => array]
     */
    public function sendToMultipleTokens(array $tokens, string $title, string $body, array $data = []): array
    {
        $result = [
            'success' => 0,
            'failure' => 0,
            'invalid_tokens' => []
        ];

        if (empty($tokens)) {
            return $result;
        }

        $notification = FCMNotification::create($title, $body);

        // Process in batches
        $batchSize = config('firebase.fcm.batch_size', 500);
        $chunks = array_chunk($tokens, $batchSize);

        foreach ($chunks as $chunk) {
            try {
                $message = CloudMessage::new()
                    ->withNotification($notification)
                    ->withData($data);


                $sendReport = $this->messaging->sendMulticast($message, $chunk);

                // dd($sendReport);

                $result['success'] += $sendReport->successes()->count();
                $result['failure'] += $sendReport->failures()->count();


                // Handle failures and mark invalid tokens
                $failureIndex = 0;
                foreach ($sendReport->failures()->getItems() as $failure) {
                    $failedToken = $chunk[$failureIndex];
                    Log::warning('FCM Send Failed', [
                        'token' => $failedToken,
                        'error' => $failure->error()->getMessage()
                    ]);

                    if ($this->isInvalidTokenError($failure->error())) {
                        $result['invalid_tokens'][] = $failedToken;
                        $this->markTokenAsInactive($failedToken);
                    }
                    $failureIndex++;
                }
            } catch (\Exception $e) {
                Log::error('FCM batch send failed: ' . $e->getMessage());
                $result['failure'] += count($chunk);
            }
        }

        return $result;
    }

    /**
     * Send notification to all tokens of specific users.
     *
     * @param array $userIds Array of user IDs
     * @param string $titleAr Arabic title
     * @param string $titleEn English title
     * @param string $bodyAr Arabic body
     * @param string $bodyEn English body
     * @param array $data Additional data
     * @return array Send result statistics
     */
    public function sendToUsers(array $userIds, string $titleAr, string $titleEn, string $bodyAr, string $bodyEn, array $data = []): array
    {
        $tokens = FcmToken::whereIn('user_id', $userIds)
            ->where('is_active', true)
            ->pluck('token')
            ->toArray();

        // For now, send English version (can be enhanced to detect user language preference)
        return $this->sendToMultipleTokens($tokens, $titleEn, $bodyEn, array_merge($data, [
            'title_ar' => $titleAr,
            'title_en' => $titleEn,
            'body_ar' => $bodyAr,
            'body_en' => $bodyEn,
        ]));
    }

    /**
     * Send notification to all users.
     *
     * @param string $titleAr Arabic title
     * @param string $titleEn English title
     * @param string $bodyAr Arabic body
     * @param string $bodyEn English body
     * @param array $data Additional data
     * @return array Send result statistics
     */
    public function sendToAllUsers(string $titleAr, string $titleEn, string $bodyAr, string $bodyEn, array $data = []): array
    {
        $tokens = FcmToken::where('is_active', true)
            ->pluck('token')
            ->toArray();

        return $this->sendToMultipleTokens($tokens, $titleEn, $bodyEn, array_merge($data, [
            'title_ar' => $titleAr,
            'title_en' => $titleEn,
            'body_ar' => $bodyAr,
            'body_en' => $bodyEn,
        ]));
    }

    /**
     * Send notification to users by role/type.
     *
     * @param string $userType User type (user, vendor, admin)
     * @param string $titleAr Arabic title
     * @param string $titleEn English title
     * @param string $bodyAr Arabic body
     * @param string $bodyEn English body
     * @param array $data Additional data
     * @return array Send result statistics
     */
    public function sendToUserType(string $userType, string $titleAr, string $titleEn, string $bodyAr, string $bodyEn, array $data = []): array
    {
        $tokens = FcmToken::where('is_active', true)
            ->whereHas('user', function ($query) use ($userType) {
                $query->where('type', $userType);
            })
            ->pluck('token')
            ->toArray();

        return $this->sendToMultipleTokens($tokens, $titleEn, $bodyEn, array_merge($data, [
            'title_ar' => $titleAr,
            'title_en' => $titleEn,
            'body_ar' => $bodyAr,
            'body_en' => $bodyEn,
        ]));
    }

    /**
     * Check if the error is due to invalid token.
     */
    protected function isInvalidTokenError(\Exception $e): bool
    {
        $message = $e->getMessage();
        return str_contains($message, 'not-found') ||
            str_contains($message, 'invalid-argument') ||
            str_contains($message, 'registration-token-not-registered');
    }

    /**
     * Mark a token as inactive in the database.
     */
    protected function markTokenAsInactive(string $token): void
    {
        FcmToken::where('token', $token)->update(['is_active' => false]);
    }

    /**
     * Clean up all invalid tokens.
     */
    public function cleanupInvalidTokens(): int
    {
        return FcmToken::where('is_active', false)
            ->where('updated_at', '<', now()->subDays(30))
            ->delete();
    }
}
