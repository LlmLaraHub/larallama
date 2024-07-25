<?php

namespace LlmLaraHub\LlmDriver\Functions\Reports;

use App\Domains\Messages\RoleEnum;
use App\Domains\Prompts\ReportingSummaryPrompt;
use App\Models\Report;
use LlmLaraHub\LlmDriver\LlmDriverFacade;
use LlmLaraHub\LlmDriver\Responses\CompletionResponse;
use LlmLaraHub\LlmDriver\ToolsHelper;

class SummarizeReport
{
    use ToolsHelper;

    protected array $promptHistory = [];

    public function handle(Report $report)
    {
        $message = $report->message;

        notify_ui($message->getChat(), 'Building Summary');

        $response = $this->summarizeReport($report);

        $assistantMessage = $message->getChat()->addInput(
            message: $response->content,
            role: RoleEnum::Assistant,
            systemPrompt: $message->getChat()->getChatable()->systemPrompt(),
            show_in_thread: true,
            meta_data: $message->meta_data,
            tools: $message->tools
        );

        $this->savePromptHistory($assistantMessage,
            implode("\n", $this->promptHistory));

        $report->user_message_id = $report->message_id;
        $report->message_id = $assistantMessage->id;
        $report->save();

        notify_ui($message->getChat(), 'Building Solutions list');
        notify_ui_report($report, 'Building Solutions list');
        notify_ui_complete($report->getChat());
    }

    protected function summarizeReport(Report $report): CompletionResponse
    {
        $sectionContent = $report->refresh()->sections->pluck('content')->toArray();
        $sectionContent = implode("\n", $sectionContent);

        $prompt = ReportingSummaryPrompt::prompt($sectionContent);

        $this->promptHistory = [$prompt];

        /** @var CompletionResponse $response */
        $response = LlmDriverFacade::driver(
            $report->getChatable()->getDriver()
        )->completion($prompt);

        return $response;
    }
}
