<?php

namespace LlmLaraHub\LlmDriver\Functions;

use App\Models\Document;
use App\Models\Report;
use App\Models\Section;
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
        $dto = FunctionDto::from([
            'name' => 'reporting_json',
            'description' => 'JSON Summary of the report',
            'parameters' => ParametersDto::from([
                'type' => 'array',
                'properties' => [
                    PropertyDto::from([
                        'name' => 'title',
                        'description' => 'The title of the section',
                        'type' => 'string',
                        'required' => true,
                    ]),
                    PropertyDto::from([
                        'name' => 'content',
                        'description' => 'The content of the section',
                        'type' => 'string',
                        'required' => true,
                    ]),
                ]
            ])
        ]);

        Log::info('LlmDriver::ClaudeClient::poolPrompt', [
            'driver' => $report->getDriver(),
            'dto' => $dto,
        ]);
        $results = LlmDriverFacade::driver($report->getDriver())
            ->setForceTool($dto)
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
