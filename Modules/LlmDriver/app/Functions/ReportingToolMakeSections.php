<?php

namespace LlmLaraHub\LlmDriver\Functions;

use App\Models\Document;
use App\Models\Report;
use App\Models\Section;
use Illuminate\Bus\Batch;
use Illuminate\Support\Facades\Log;
use LlmLaraHub\LlmDriver\LlmDriverFacade;

class ReportingToolMakeSections
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
        $results = LlmDriverFacade::driver($report->getDriver())
            ->completionPool($prompts);
        foreach ($results as $resultIndex => $result) {
            $content = $result->content;
            $this->makeSectionFromContent($content, $document, $report);
        }
    }

    protected function makeSectionFromContent(
        string $content,
        Document $document,
        Report $report): void
    {
        try {

            notify_ui($report->getChat(), 'Building Requirements list');
            notify_ui_report($report, 'Building Requirements list');

            /**
             * @TODO
             * use the force tool feature to then
             * make a tool that it has to return the values
             * as
             */
            $content = str($content)
                ->remove('```json')
                ->remove('```')
                ->toString();
            $contentDecoded = json_decode($content, true);
            foreach ($contentDecoded as $sectionIndex => $sectionText) {
                $title = data_get($sectionText, 'title', 'NOT TITLE GIVEN');
                $contentBody = data_get($sectionText, 'content', 'NOT CONTENT GIVEN');
                Section::updateOrCreate([
                    'document_id' => $document->id,
                    'report_id' => $report->id,
                    'sort_order' => $report->refresh()->sections->count() + 1,
                ], [
                    'subject' => $title,
                    'content' => $contentBody,
                ]);
                notify_ui_report($report, 'Added Requirement');
            }
        } catch (\Exception $e) {
            Section::updateOrCreate([
                'document_id' => $document->id,
                'report_id' => $report->id,
                'sort_order' => $report->refresh()->sections->count() + 1,
            ], [
                'subject' => '[ERROR FORMATTING PLEASE FIX]',
                'content' => $content,
            ]);
            notify_ui_report($report, 'Added Requirement');
            Log::error('Error parsing JSON', [
                'error' => $e->getMessage(),
                'content' => $content,
                'line' => $e->getLine(),
            ]);
        }

        notify_ui($report->getChat(), 'Done Building Requirements list');
        notify_ui_report($report, 'Done Building Requirements list');
    }

}
