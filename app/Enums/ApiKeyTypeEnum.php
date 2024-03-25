<?php

namespace App\Enums;

use ArchTech\Enums\Values;

enum ApiKeyTypeEnum: string
{
    use Values;

    case Openai = 'openai';
    case OpenAi_Azure = 'openai_azure';
    case Ollama = 'ollama';
    case Claude = 'claude';
    case Gemini = 'gemini';
}
