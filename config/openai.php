<?php

return [
    /*
    |--------------------------------------------------------------------------
    | OpenAI Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for OpenAI API integration used for AI-powered features
    | including city insights, job matching, resume optimization, and content generation.
    |
    */

    'api_key' => env('OPENAI_API_KEY'),

    'organization' => env('OPENAI_ORGANIZATION'),

    'timeout' => env('OPENAI_TIMEOUT', 30),

    'max_tokens' => env('OPENAI_MAX_TOKENS', 2000),

    'temperature' => env('OPENAI_TEMPERATURE', 0.7),

    'model' => env('OPENAI_MODEL', 'gpt-3.5-turbo'),

    'models' => [
        'gpt-3.5-turbo' => 'GPT-3.5 Turbo',
        'gpt-4' => 'GPT-4',
        'gpt-4-turbo' => 'GPT-4 Turbo',
    ],

    /*
    |--------------------------------------------------------------------------
    | Caching Configuration
    |--------------------------------------------------------------------------
    |
    | Cache duration for AI responses to reduce API costs and improve performance.
    |
    */

    'cache_duration' => env('OPENAI_CACHE_DURATION', 3600), // 1 hour in seconds

    /*
    |--------------------------------------------------------------------------
    | Fallback Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for fallback responses when OpenAI is unavailable.
    |
    */

    'enable_fallbacks' => env('OPENAI_ENABLE_FALLBACKS', true),

    'default_prompts' => [
        'city_summary' => 'Generate a comprehensive summary for a digital nomad city, including cost of living, internet quality, safety, visa requirements, and nomad-friendly amenities.',
        'city_comparison' => 'Compare two digital nomad cities across key factors like cost, internet, safety, climate, and nomad amenities.',
        'city_recommendation' => 'Recommend digital nomad cities based on user preferences including budget, climate, internet requirements, and lifestyle preferences.',
        'city_insights' => 'Generate insights about a digital nomad city including pros/cons, best neighborhoods, and tips for nomads.',
    ],

    'fallback_responses' => [
        'job_matching' => [
            'overall_score' => 70,
            'skills_score' => 65,
            'experience_score' => 75,
            'location_score' => 80,
            'salary_score' => 70,
            'culture_score' => 75,
            'insights' => 'Basic job matching analysis based on profile data.',
            'strengths' => ['Relevant background', 'Good experience level'],
            'improvements' => ['Consider skill development'],
            'tips' => ['Tailor your application to highlight relevant experience'],
        ],
        'resume_optimization' => [
            'optimized_resume' => 'Resume optimization temporarily unavailable. Please try again later.',
            'changes_made' => ['Basic formatting improvements'],
            'skills_to_highlight' => ['Technical skills', 'Relevant experience'],
            'keywords' => ['Remote work', 'Digital nomad'],
        ],
        'cover_letter' => [
            'cover_letter' => 'Cover letter generation temporarily unavailable. Please try again later.',
            'key_points' => ['Relevant experience', 'Company interest'],
        ],
        'skills_extraction' => ['Remote work', 'Communication', 'Problem solving'],
    ],
];