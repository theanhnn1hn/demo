<?php
/**
 * General configuration
 */
return [
    'app' => [
        'name' => 'YouTube Processor',
        'url' => $_ENV['APP_URL'] ?? 'http://localhost',
        'env' => $_ENV['APP_ENV'] ?? 'development',
        'debug' => $_ENV['APP_DEBUG'] ?? true,
        'timezone' => 'Asia/Ho_Chi_Minh',
    ],
    
    'storage' => [
        'videos' => 'storage/videos',
        'subtitles' => 'storage/subtitles',
        'images' => 'storage/images',
        'exports' => 'storage/exports',
    ],
    
    'processing' => [
        'max_video_duration' => 3600, // Maximum video duration in seconds (1 hour)
        'max_video_size' => 1024 * 1024 * 500, // Maximum video size in bytes (500 MB)
        'allowed_video_formats' => ['mp4', 'webm', 'mkv'],
        'default_language' => 'vi', // Default language for speech to text
        'default_tone' => 'informative', // Default tone for content rewriting
    ],
    
    'scan' => [
        'max_videos_per_scan' => 10, // Maximum number of videos to process per scan
        'frequency_options' => [
            'hourly' => 3600,
            '6_hours' => 21600,
            '12_hours' => 43200,
            'daily' => 86400,
            'weekly' => 604800,
        ],
    ],
    
    'image' => [
        'default_style' => 'realistic',
        'default_width' => 1024,
        'default_height' => 1024,
        'default_prompt_template' => 'Professional cinematic style photo, ultra-detailed, 8k, dramatic lighting, [theme] visual storytelling, high-quality, high-definition',
    ],
    
    'users' => [
        'default_api_limit' => 100, // Default API usage limit for new users
        'default_role' => 'user', // Default role for new users
    ],
];
