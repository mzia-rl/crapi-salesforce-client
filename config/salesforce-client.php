<?php return [

    // General
    'api_version' => env('SALESFORCE_API_VERSION', 'v51.0'),

    // Client Credentials
    'client_id' => env('SALESFORCE_CLIENT_ID'),
    'client_secret' => env('SALESFORCE_CLIENT_SECRET'),
    'security_token' => env('SALESFORCE_SECURITY_TOKEN'),

    // User Credentials
    'user' => [
        'username' => env('SALESFORCE_USERNAME'),
        'password' => env('SALESFORCE_PASSWORD')
    ]

];