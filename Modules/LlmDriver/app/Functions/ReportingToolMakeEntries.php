<?php

namespace LlmLaraHub\LlmDriver\Functions;

use App\Domains\Chat\MetaDataDto;
use App\Domains\Prompts\FindSolutionsPrompt;
use App\Domains\Reporting\EntryTypeEnum;
use App\Domains\Reporting\StatusEnum;
use App\Models\Collection;
use App\Models\DocumentChunk;
use App\Models\Entry;
use App\Models\Report;
use App\Models\Section;
use Illuminate\Support\Facades\Log;
use LlmLaraHub\LlmDriver\DistanceQuery\DistanceQueryFacade;
use LlmLaraHub\LlmDriver\LlmDriverFacade;

class ReportingToolMakeEntries
{
    public function handle(Report $report)
    {
        $referenceCollection = $report->reference_collection;

        if (! $referenceCollection) {
            return;
        }

        foreach ($report->refresh()->sections->chunk(3) as $sectionChunk) {

            [$prompts, $sections] = $this->buildPrompts($sectionChunk, $report, $referenceCollection);

            $results = LlmDriverFacade::driver($report->getDriver())
                ->completionPool($prompts);

            $this->processResults($sections, $results, $report);
        }

        $report->update([
            'status_entries_generation' => StatusEnum::Complete,
        ]);

        notify_ui_report_complete($report);
        notify_ui_complete($report);
    }

    protected function processResults(array $sections, array $results, Report $report): void
    {
        foreach ($results as $resultIndex => $result) {
            $section = data_get($sections, $resultIndex, null);

            if (! $section) {
                continue;
            }

            $content = $result->content;
            $title = str($content)->limit(125)->toString();

            Entry::updateOrCreate([
                'section_id' => $section->id,
                'document_id' => $section->document_id,
            ],
                [
                    'title' => $title,
                    'content' => $content,
                    'type' => EntryTypeEnum::Solution,
                ]);

            notify_ui_report($report, 'Added Solution');
        }
    }

    protected function buildPrompts(\Illuminate\Support\Collection $sectionChunk, Report $report, Collection $referenceCollection): array
    {
        $prompts = [];
        $sections = [];

        /** @var Section $section */
        foreach ($sectionChunk as $section) {
            try {
                $input = $section->content;

                $embedding = LlmDriverFacade::driver(
                    $report->getEmbeddingDriver()
                )->embedData($input);

                $embeddingSize = get_embedding_size($report->getEmbeddingDriver());

                /** @phpstan-ignore-next-line */
                $documentChunkResults = DistanceQueryFacade::cosineDistance(
                    $embeddingSize,
                    $referenceCollection->id,
                    $embedding->embedding,
                    MetaDataDto::from([])//@NOTE could use this later if needed
                );

                $content = [];
                /** @var DocumentChunk $result */
                foreach ($documentChunkResults as $result) {
                    $contentString = remove_ascii($result->content);
                    $content[] = $contentString; //reduce_text_size seem to mess up Claude?
                }

                $context = implode(' ', $content);

                $prompt = FindSolutionsPrompt::prompt(
                    $section->content,
                    $context,
                    $report->getChatable()->description
                );

                $prompts[] = $prompt;
                $sections[] = $section;
            } catch (\Exception $e) {
                Log::error('Error running Reporting Tool Checker for Section', [
                    'error' => $e->getMessage(),
                    'section' => $section->id,
                    'line' => $e->getLine(),
                ]);
            }
        }

        return [$prompts, $sections];
    }
}
