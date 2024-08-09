<?php

namespace App\Jobs;

use App\Domains\Messages\RoleEnum;
use App\Domains\Prompts\VerifyPrompt;
use App\Domains\Reporting\StatusEnum;
use Facades\App\Domains\Tokenizer\Templatizer;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use LlmLaraHub\LlmDriver\LlmDriverFacade;
use LlmLaraHub\LlmDriver\ToolsHelper;

class GatherInfoFinalPromptJob implements ShouldQueue
{
    use Batchable, ToolsHelper;
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public \App\Models\Report $report
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $messages = [];

        $context = [];

        foreach ($this->report->sections as $section) {
            $context[] = $section->content;
        }

        $context = implode("\n", $context);

        $prompt = Templatizer::appendContext(true)
            ->handle($this->report->message->getContent(), $context);

        $response = LlmDriverFacade::driver($this->report->getDriver())
            ->completion($prompt);

        Log::info('GatherInfoReportSectionsJob doing one more check', [
            'response' => $response->content,
        ]);

        $prompt = VerifyPrompt::prompt(
            originalResults: $response->content,
            context: $context);

        $response = LlmDriverFacade::driver($this->report->getDriver())
            ->completion($prompt);

        $assistantMessage = $this->report->getChat()->addInput(
            message: $response->content,
            role: RoleEnum::Assistant,
            systemPrompt: 'You are an assistant helping with this gathered info.',
            show_in_thread: true,
            meta_data: $this->report->message->meta_data,
            tools: $this->report->message->tools
        );

        $this->report->user_message_id = $this->report->message_id;
        $this->report->message_id = $assistantMessage->id;
        $this->report->status_entries_generation = StatusEnum::Complete;
        $this->report->save();

        $this->savePromptHistory($assistantMessage, $prompt);

        notify_ui($this->report->getChat(), 'Building Solutions list');
        notify_ui_report($this->report, 'Building Solutions list');
        notify_ui_complete($this->report->getChat());

    }
}
