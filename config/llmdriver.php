<?php

return [
    'driver' => env('LLM_DRIVER', 'mock'),

    'embedding_sizes' => [
        'mock' => 4096,
        'text-embedding-3-large' => 3072,
        'text-embedding-3-medium' => 768,
        'text-embedding-3-small' => 384,
        'llama2' => 4096,
        'mistral' => 4096,
    ],

    'drivers' => [
        'mock' => [
            'embedding_model' => "mock"
        ],
        'openai' => [
            'api_key' => env('OPENAI_API_KEY'),
            'api_url' => env('OPENAI_API_URL', 'https://api.openai.com/v1'),
            'embedding_model' => env('OPENAI_EMBEDDING_MODEL', 'text-embedding-3-large'),
            'completion_model' => env('OPENAI_COMPLETION_MODEL', 'gpt-4-turbo-preview'),
            'chat_model' => env('OPENAICHAT_MODEL', 'gpt-4-turbo-preview'),
        ],
        'claude' => [
            'api_key' => env('CLAUDE_API_KEY'),
            'max_tokens' => env('CLAUDE_MAX_TOKENS', 4096),
            'models' => [
                //@see https://www.anthropic.com/news/claude-3-family
                'completion_model' => env('CLAUDE_COMPLETION_MODEL', 'claude-3-haiku-20240307'),
            ],
        ],
        'ollama' => [
            'feature_flags' => [
                'functions' => env('OLLAMA_FUNCTIONS', false),
            ],
            'api_key' => 'ollama',
            'api_url' => env('OLLAMA_API_URL', 'http://127.0.0.1:11434/api/'),
            'models' => [
                //@see https://github.com/ollama/ollama/blob/main/docs/openai.md
                'completion_model' => env('OLLAMA_COMPLETION_MODEL', 'llama2'),
                'embedding_model' => env('OLLAMA_EMBEDDING_MODEL', 'llama2'),
            ],
        ],
    ],
];
