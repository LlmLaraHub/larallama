<?php

namespace LlmLaraHub\LlmDriver;

use App\Models\Chat;
use Facades\App\Domains\Messages\RetrieveRelatedChatRepo;
use Illuminate\Support\Facades\Log;

class SimpleRetrieveRelatedOrchestrate
{
    protected string $response = '';

    protected bool $requiresFollowup = false;

    public function handle(string $message, Chat $chat): ?string
    {
        Log::info('[LaraChain] Skipping over functions doing search and summarize');

        notify_ui(
            $chat->chatable,
            'Searching data now to summarize content'
        );
        $response = RetrieveRelatedChatRepo::search($chat, $message);

        return $response;
    }

    protected function hasFunctions(array $functions): bool
    {
        return is_array($functions) && count($functions) > 0;
    }
}
