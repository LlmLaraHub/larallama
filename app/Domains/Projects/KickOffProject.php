<?php

namespace App\Domains\Projects;

use App\Domains\Chat\UiStatusEnum;
use App\Models\Project;
use Facades\App\Domains\Projects\Orchestrate;

class KickOffProject
{
    public function handle(Project $project)
    {
        $chat = $project->chats()->first();

        $chat->updateQuietly([
            'chat_status' => UiStatusEnum::InProgress,
        ]);

        $chat->messages()->delete();

        $project->tasks()->delete();

        Orchestrate::handle($chat, $project->getContent(), $project->getSystemPrompt());

        $chat->updateQuietly([
            'chat_status' => UiStatusEnum::Complete,
        ]);
    }
}
