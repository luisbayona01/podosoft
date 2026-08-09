<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'gemini' => [
        'key' => env('GEMINI_API_KEY'),
        'model' => env('GEMINI_MODEL', 'gemma-4-31b-it'),
    ],

    'nvidia_nim' => [
        'api_key' => env('NVIDIA_NIM_API_KEY'),
        'base_url' => env('NVIDIA_NIM_BASE_URL', 'https://integrate.api.nvidia.com/v1'),
        'model' => env('NVIDIA_NIM_MODEL', 'meta/llama-3.1-8b-instruct'),
        'timeout' => env('NVIDIA_NIM_TIMEOUT', 120),
        'temperature' => env('NVIDIA_NIM_TEMPERATURE', 0.2),
        'top_p' => env('NVIDIA_NIM_TOP_P', 0.7),
        'max_tokens' => env('NVIDIA_NIM_MAX_TOKENS', 1024),
    ],

    'agent' => [
        'api_key' => env('NVIDIA_NIM_API_KEY'),
        'base_url' => env('NVIDIA_NIM_BASE_URL', 'https://integrate.api.nvidia.com/v1'),
        'model' => env('NVIDIA_NIM_MODEL', 'meta/llama-3.1-8b-instruct'),
        'timeout' => env('NVIDIA_NIM_TIMEOUT', 120),
        'temperature' => env('NVIDIA_NIM_TEMPERATURE', 0.2),
        'top_p' => env('NVIDIA_NIM_TOP_P', 0.7),
        'max_tokens' => env('NVIDIA_NIM_MAX_TOKENS', 1024),
    ],

    'evolution' => [
        'key' => env('EVOLUTION_API_KEY'),
        'url' => env('EVOLUTION_API_URL'),
    ],

    'python_ai' => [
        'url' => env('PYTHON_AI_URL'),
        'timeout' => env('PYTHON_AI_TIMEOUT', 30),
        'token' => env('PYTHON_AI_TOKEN'),
    ],

];
