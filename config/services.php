<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of configuration.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Rule Engine Service Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for the external rule engine microservice that handles
    | the evaluation of promotion rules.
    |
    */

    'rule_engine' => [
        'url' => env('RULE_ENGINE_SERVICE_URL', 'http://localhost:5000/api'),
        'timeout' => env('RULE_ENGINE_TIMEOUT', 30),
        'retries' => env('RULE_ENGINE_RETRIES', 3),
        'retry_delay' => env('RULE_ENGINE_RETRY_DELAY', 1000), // milliseconds
    ],

];