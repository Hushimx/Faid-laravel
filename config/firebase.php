<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Firebase Service Account
    |--------------------------------------------------------------------------
    |
    | Path to your Firebase service account JSON file.
    | Download from: Firebase Console → Project Settings → Service Accounts
    |
    */
    'credentials' => env('FIREBASE_CREDENTIALS', storage_path('app/firebase/service-account.json')),

    /*
    |--------------------------------------------------------------------------
    | Firebase Database URL
    |--------------------------------------------------------------------------
    |
    | Your Firebase Realtime Database URL (optional, only if using database)
    |
    */
    'database_url' => env('FIREBASE_DATABASE_URL', ''),

    /*
    |--------------------------------------------------------------------------
    | FCM Options
    |--------------------------------------------------------------------------
    |
    | Default options for Firebase Cloud Messaging
    |
    */
    'fcm' => [
        'batch_size' => 500, // Maximum tokens per batch request
        'validate_only' => false, // Set to true to test without sending
    ],

    /*
    |--------------------------------------------------------------------------
    | Notification Defaults
    |--------------------------------------------------------------------------
    |
    | Default notification settings
    |
    */
    'notification' => [
        'sound' => 'default',
        'badge' => '1',
        'priority' => 'high', // high or normal
    ],
];
