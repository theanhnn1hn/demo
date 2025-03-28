<?php
/**
 * API keys configuration
 */
return [
    'youtube' => [
        'api_key' => $_ENV['YOUTUBE_API_KEY'] ?? '',
        'client_id' => $_ENV['YOUTUBE_CLIENT_ID'] ?? '',
        'client_secret' => $_ENV['YOUTUBE_CLIENT_SECRET'] ?? '',
    ],
    
    'speech_to_text' => [
        'assembly_ai' => [
            'api_key' => $_ENV['ASSEMBLY_AI_KEY'] ?? '',
        ],
        'rev_ai' => [
            'api_key' => $_ENV['REV_AI_KEY'] ?? '',
        ],
        'whisper' => [
            'api_key' => $_ENV['OPENAI_API_KEY'] ?? '',
        ],
    ],
    
    'ai_content' => [
        'openai' => [
            'api_key' => $_ENV['OPENAI_API_KEY'] ?? '',
        ],
        'claude' => [
            'api_key' => $_ENV['ANTHROPIC_API_KEY'] ?? '',
        ],
    ],
    
    'image_generation' => [
        'dall_e' => [
            'api_key' => $_ENV['OPENAI_API_KEY'] ?? '',
        ],
        'midjourney' => [
            'api_key' => $_ENV['MIDJOURNEY_API_KEY'] ?? '',
        ],
        'stable_diffusion' => [
            'api_key' => $_ENV['STABILITY_API_KEY'] ?? '',
        ],
    ],
];
