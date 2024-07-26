<?php

namespace LlmLaraHub\LlmDriver\Functions;

use App\Models\Document;
use App\Models\Report;
use App\Models\Section;
use Illuminate\Support\Facades\Log;
use LlmLaraHub\LlmDriver\LlmDriverFacade;

class GatherInfoToolMakeSections
{
    public function handle(
        array $prompts,
        Report $report,
        Document $document
    ) {

        $this->poolPrompt($prompts, $report, $document);

    }

    protected function poolPrompt(array $prompts, Report $report, Document $document): void
    {
        notify_ui($report->getChat(), 'Running Prompt against gathered info');
        notify_ui_report($report, 'Running Prompt against gathered info');

        Log::info('LlmDriver::GatherInfoToolMakeSections::poolPrompt', [
            'driver' => $report->getDriver(),
            'prompts' => $prompts,
        ]);

        $results = LlmDriverFacade::driver($report->getDriver())
            ->completionPool($prompts);

        foreach ($results as $resultIndex => $result) {
            $content = $result->content;
            $prompt = data_get($prompts, $resultIndex, 'no prompt found');
            $this->makeSectionFromContent(
                $content,
                $document,
                $report,
                $prompt);
        }

        notify_ui($report->getChat(), 'Done gathering info');
        notify_ui_report($report, 'Done gathering info');
    }

    protected function makeSectionFromContent(
        string $content,
        Document $document,
        Report $report,
        string $prompt): void
    {
        try {

            Section::updateOrCreate([
                'document_id' => $document->id,
                'report_id' => $report->id,
                'sort_order' => $report->refresh()->sections->count() + 1,
            ], [
                'subject' => str($content)->limit(128)->toString(),
                'content' => $content,
                'prompt' => $prompt,
            ]);

        } catch (\Exception $e) {
            Log::error('Error creating section', [
                'error' => $e->getMessage(),
                'content' => $content,
                'line' => $e->getLine(),
            ]);
        }
    }
}
