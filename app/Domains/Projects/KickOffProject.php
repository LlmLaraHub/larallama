<?php

namespace App\Domains\Projects;

use App\Domains\Campaigns\CampaignKickOffPrompt;
use App\Domains\Campaigns\ChatStatusEnum;
use App\Models\Campaign;
use Facades\App\Services\LlmServices\Orchestration\Orchestrate;

class KickOffProject
{
    public function handle(Campaign $campaign)
    {
        $campaign->updateQuietly([
            'chat_status' => ChatStatusEnum::InProgress,
        ]);

        $campaign->messages()->delete();

        $campaign->tasks()->delete();

        $campaignContext = $campaign->getContext();

        $prompt = CampaignKickOffPrompt::getPrompt($campaignContext);

        Orchestrate::handle($campaign, $prompt);

        $campaign->updateQuietly([
            'chat_status' => ChatStatusEnum::Complete,
        ]);
    }
}
