<?php

namespace LlmLaraHub\LlmDriver;

use App\Domains\Chat\ToolsDto;
use App\Models\Message;
use LlmLaraHub\LlmDriver\Functions\FunctionCallDto;

trait ToolsHelper
{
    protected function addToolsToMessage(Message $message, FunctionCallDto $functionDto): Message
    {
        $tools = $message->tools;
        if (! $tools) {
            $tools = ToolsDto::from(['tools' => []]);
        }
        $tools->tools[] = $functionDto;
        $message->updateQuietly(['tools' => $tools]);

        return $message->refresh();
    }
}
