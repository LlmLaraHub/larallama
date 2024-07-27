<?php

namespace App\Jobs;

use App\Domains\Messages\RoleEnum;
use App\Domains\Reporting\StatusEnum;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use LlmLaraHub\LlmDriver\LlmDriverFacade;
use LlmLaraHub\LlmDriver\Requests\MessageInDto;
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

        $history = [];
        foreach ($this->report->sections as $section) {
            $messages[] = MessageInDto::from([
                'content' => $section->content,
                'role' => 'user',
            ]);

            $history[] = $section->content;

            $messages[] = MessageInDto::from([
                'content' => 'Using the surrounding context to continue this response thread',
                'role' => 'assistant',
            ]);
        }

        $messages[] = MessageInDto::from([
            'content' => sprintf('Using the context of this chat can you '.
                $this->report->message->getPrompt()),
            'role' => 'user',
        ]);

        $response = LlmDriverFacade::driver($this->report->getDriver())
            ->chat($messages);

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

        $this->savePromptHistory($assistantMessage,
            implode("\n", $history));

        notify_ui($this->report->getChat(), 'Building Solutions list');
        notify_ui_report($this->report, 'Building Solutions list');
        notify_ui_complete($this->report->getChat());

    }
}
