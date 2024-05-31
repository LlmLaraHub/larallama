<?php

return [
    'driver' => env('LLM_DRIVER', 'mock'),

    'distance_driver' => env('LLM_DISTANCE_DRIVER', 'post_gres'),

    'chunking' => [
        'default_size' => env('CHUNK_SIZE', 600),
    ],
    'embedding_sizes' => [
        'mock' => 4096,
        'text-embedding-3-large' => 3072,
        'text-embedding-3-medium' => 768,
        'text-embedding-3-small' => 384,
        'ollama' => 4096,
        'llama2' => 4096,
        'llama3' => 4096,
        'mistral' => 4096,
        'mxbai-embed-large' => 1024,
    ],

    'drivers' => [
        'mock' => [
            'models' => [
                'completion_model' => 'mock',
                'embedding_model' => 'mock',
            ],
        ],
        'openai' => [
            'api_key' => env('OPENAI_API_KEY'),
            'api_url' => env('OPENAI_API_URL', 'https://api.openai.com/v1'),
            'models' => [
                'embedding_model' => env('OPENAI_EMBEDDING_MODEL', 'text-embedding-3-large'),
                'completion_model' => env('OPENAI_COMPLETION_MODEL', 'gpt-4-turbo-preview'),
                'chat_model' => env('OPENAICHAT_MODEL', 'gpt-4-turbo-preview'),
            ],
        ],
        'claude' => [
            'api_key' => env('CLAUDE_API_KEY'),
            'max_tokens' => env('CLAUDE_MAX_TOKENS', 4096),
            'models' => [
                //@see https://www.anthropic.com/news/claude-3-family
                'completion_model' => env('CLAUDE_COMPLETION_MODEL', 'claude-3-haiku-20240307'),
            ],
        ],
        'groq' => [
            'api_key' => env('GROQ_API_KEY'),
            'max_tokens' => env('GROQ_MAX_TOKENS', 32000),
            'models' => [
                //@see https://www.anthropic.com/news/claude-3-family
                'completion_model' => env('GROQ_COMPLETION_MODEL', 'mixtral-8x7b-32768'),
            ],
        ],
        'ollama' => [
            'feature_flags' => [
                'functions' => env('OLLAMA_FUNCTIONS', false),
            ],
            'api_key' => 'ollama',
            'api_url' => env('OLLAMA_API_URL', 'http://localhost:11434/api/'),
            'models' => [
                //@see https://github.com/ollama/ollama/blob/main/docs/openai.md
                'completion_model' => env('OLLAMA_COMPLETION_MODEL', 'llama3'),
                'embedding_model' => env('OLLAMA_EMBEDDING_MODEL', 'mxbai-embed-large'),
                'chat_output_model' => env('OLLAMA_COMPLETION_MODEL', 'llama3'), //this is good to use other systems for better repsonses to people in chat
            ],
        ],
    ],
    'features' => [
        'pptx' => env('FEATURE_PPTX', false),
    ],
    'sources' => [
        'search_driver' => env('LARALAMMA_SEARCH_SOURCE', 'mock'),
        'search' => [
            'drivers' => [
                'mock',
                'brave',
            ],
        ],
        'config' => [
            'mock' => [
                'api_token' => 'foobar',
            ],
            'brave' => [
                'api_token' => env('BRAVE_API_TOKEN', false),
            ],
        ],
    ],
];
