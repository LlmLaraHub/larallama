<?php

return [
    'driver' => env('LLM_DRIVER', 'mock'),

    'drivers' => [
        'mock' => [

        ],
        'openai' => [
            'api_key' => env('OPENAI_API_KEY'),
            'api_url' => env('OPENAI_API_URL', 'https://api.openai.com/v1/engines/davinci-codex/completions'),
            'embedding_model' => env('OPENAI_EMBEDDING_MODEL', 'text-embedding-3-large'),
            'completion_model' => env('OPENAI_COMPLETION_MODEL', 'gpt-4-turbo-preview'),
        ],
        'azure' => [

        ],
        'ollama' => [

        ],
    ],
];
