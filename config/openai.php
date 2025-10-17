<?php

return [
    /*
    |--------------------------------------------------------------------------
    | OpenAI Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for OpenAI API integration used for AI-powered features
    | including job matching, resume optimization, and content generation.
    |
    */

    'api_key' => env('OPENAI_API_KEY'),
    'model' => env('OPENAI_MODEL', 'gpt-3.5-turbo'),
    'max_tokens' => env('OPENAI_MAX_TOKENS', 2000),
    'temperature' => env('OPENAI_TEMPERATURE', 0.7),
    
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
            'tips' => ['Tailor your application to highlight relevant experience']
        ],
        'resume_optimization' => [
            'optimized_resume' => 'Resume optimization temporarily unavailable. Please try again later.',
            'changes_made' => ['Basic formatting improvements'],
            'skills_to_highlight' => ['Technical skills', 'Relevant experience'],
            'keywords' => ['Remote work', 'Digital nomad']
        ],
        'cover_letter' => [
            'cover_letter' => 'Cover letter generation temporarily unavailable. Please try again later.',
            'key_points' => ['Relevant experience', 'Company interest']
        ],
        'skills_extraction' => ['Remote work', 'Communication', 'Problem solving']
    ],
];
