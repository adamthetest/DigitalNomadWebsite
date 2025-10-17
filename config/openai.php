<?php

return [
    /*
    |--------------------------------------------------------------------------
    | OpenAI API Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration for the OpenAI API integration.
    | You can set your API key in the .env file as OPENAI_API_KEY.
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
    
    'default_prompts' => [
        'city_summary' => 'Generate a comprehensive summary for a digital nomad city, including cost of living, internet quality, safety, visa requirements, and nomad-friendly amenities.',
        'city_comparison' => 'Compare two digital nomad cities across key factors like cost, internet, safety, climate, and nomad amenities.',
        'city_recommendation' => 'Recommend digital nomad cities based on user preferences including budget, climate, internet requirements, and lifestyle preferences.',
        'city_insights' => 'Generate insights about a digital nomad city including pros/cons, best neighborhoods, and tips for nomads.',
    ],
];
