<?php

return [

    'api_key' => env('NVIDIA_NIM_API_KEY'),
    'base_url' => env('NVIDIA_NIM_BASE_URL', 'https://integrate.api.nvidia.com/v1'),
    'model' => env('NVIDIA_NIM_MODEL', 'meta/llama-3.1-8b-instruct'),
    'timeout' => env('NVIDIA_NIM_TIMEOUT', 120),
    'temperature' => env('NVIDIA_NIM_TEMPERATURE', 0.2),
    'top_p' => env('NVIDIA_NIM_TOP_P', 0.7),
    'max_tokens' => env('NVIDIA_NIM_MAX_TOKENS', 1024),

];