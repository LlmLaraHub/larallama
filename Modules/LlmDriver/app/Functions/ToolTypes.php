<?php

namespace LlmLaraHub\LlmDriver\Functions;

use App\Helpers\EnumHelperTrait;

enum ToolTypes: string
{
    use EnumHelperTrait;

    case Chat = 'chat';
    case ChatCompletion = 'chat_completion';
    case Source = 'source';
    case Output = 'output';
    case NoFunction = 'no_function';
    case ManualChoice = 'manual_choice';
}
