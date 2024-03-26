<?php 

return [
    'driver' => env("LLM_DRIVER", "mock"),

    "drivers" => 
    [
        "mock" => [

        ],
        "openai" => [
            "api_key" => env("OPENAI_API_KEY"),
            "api_url" => env("OPENAI_API_URL", "https://api.openai.com/v1/engines/davinci-codex/completions")
        ],
        "azure" => [

        ],
        "ollama" => [

        ],
    ]
];