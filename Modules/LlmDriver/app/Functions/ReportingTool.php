<?php

namespace LlmLaraHub\LlmDriver\Functions;

use App\Domains\Chat\MetaDataDto;
use App\Domains\Messages\RoleEnum;
use App\Domains\Prompts\FindSolutionsPrompt;
use App\Domains\Prompts\ReportBuildingFindRequirementsPrompt;
use App\Domains\Prompts\ReportingSummaryPrompt;
use App\Domains\Reporting\EntryTypeEnum;
use App\Domains\Reporting\ReportTypeEnum;
use App\Domains\Reporting\StatusEnum;
use App\Models\Document;
use App\Models\DocumentChunk;
use App\Models\Entry;
use App\Models\Message;
use App\Models\Report;
use App\Models\Section;
use Illuminate\Support\Facades\Log;
use Laravel\Pennant\Feature;
use LlmLaraHub\LlmDriver\DistanceQuery\DistanceQueryFacade;
use LlmLaraHub\LlmDriver\LlmDriverFacade;
use LlmLaraHub\LlmDriver\Responses\CompletionResponse;
use LlmLaraHub\LlmDriver\Responses\EmbeddingsResponseDto;
use LlmLaraHub\LlmDriver\Responses\FunctionResponse;
use LlmLaraHub\LlmDriver\ToolsHelper;

class ReportingTool extends FunctionContract
{
    use ToolsHelper;

    protected string $name = 'reporting_tool';

    protected string $description = 'Uses Reference collection to generate a report';

    protected string $response = '';

    protected array $results = [];

    protected array $promptHistory = [];

    public function handle(
        Message $message): FunctionResponse
    {
        Log::info('[LaraChain] ReportingTool Function called');

        $report = Report::firstOrCreate([
            'chat_id' => $message->getChat()->id,
            'message_id' => $message->id,
            'reference_collection_id' => $message->getReferenceCollection()?->id,
            'user_id' => $message->getChat()->user_id,
        ], [
            'type' => ReportTypeEnum::RFP,
            'status_sections_generation' => StatusEnum::Pending,
            'status_entries_generation' => StatusEnum::Pending,
        ]);

        $documents = $message->getChatable()->documents;

        notify_ui($message->getChat(), 'Going through all the documents to check requirements');

        $this->results = [];

        Log::info('[LaraChain] - Reporting Tool', [
            'documents_count' => count($documents),
        ]);

        foreach ($documents as $index => $document) {
            try {

                $groupedChunks = $document->document_chunks
                    ->sortBy('sort_order')
                    ->groupBy('sort_order');

                $pagesGrouped = $groupedChunks->map(function ($chunks, $pageNumber) {
                    return [
                        "page_$pageNumber" => $chunks->toArray(),
                    ];
                })->collapse();

                foreach (collect($pagesGrouped)
                    ->chunk(3) as $pageIndex => $pagesChunk) {
                    $prompts = [];
                    foreach ($pagesChunk as $index => $page) {
                        $pageContent = collect($page)->pluck('content')->toArray();
                        $pageContent = implode("\n", $pageContent);
                        $prompt = ReportBuildingFindRequirementsPrompt::prompt(
                            $pageContent, $message->getContent(), $message->getChatable()->description
                        );
                        $prompts[] = $prompt;
                    }
                    $this->poolPrompt($prompts, $report, $document);
                }

            } catch (\Exception $e) {
                Log::error('Error running Reporting Tool Checker', [
                    'error' => $e->getMessage(),
                    'index' => $index,
                    'line' => $e->getLine(),
                ]);
            }
        }

        notify_ui($message->getChat(), 'Building Summary');

        $response = $this->summarizeReport($report);

        $report->update([
            'status_sections_generation' => StatusEnum::Complete,
        ]);

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

        $report->message_id = $assistantMessage->id;
        $report->save();

        notify_ui_complete($message->getChat());

        notify_ui($message->getChat(), 'Building Solutions list');
        notify_ui_report($report, 'Building Solutions list');

        $this->makeEntriesFromSections($report);

        $report->update([
            'status_entries_generation' => StatusEnum::Complete,
        ]);

        notify_ui_complete($message->getChat());
        notify_ui_report_complete($report);
        //as a final output
        //get deadlines
        //get contacts

        return FunctionResponse::from([
            'content' => $response->content,
            'prompt' => implode('\n', $this->promptHistory),
            'requires_followup' => false,
            'documentChunks' => collect([]),
            'save_to_message' => false,
        ]);
    }

    protected function makeEntriesFromSections(Report $report): void
    {
        $referenceCollection = $report->reference_collection;

        if (! $referenceCollection) {
            return;
        }

        foreach ($report->refresh()->sections->chunk(3) as $sectionChunk) {
            $prompts = []; //reset every 3 sections
            $sections = []; //reset every 3 sections

            /** @var Section $section */
            foreach ($sectionChunk as $section) {

                try {
                    $input = $section->content;

                    /** @var EmbeddingsResponseDto $embedding */
                    $embedding = LlmDriverFacade::driver(
                        $report->getEmbeddingDriver()
                    )->embedData($input);

                    $embeddingSize = get_embedding_size($report->getEmbeddingDriver());

                    /** @phpstan-ignore-next-line */
                    $documentChunkResults = DistanceQueryFacade::cosineDistance(
                        $embeddingSize,
                        /** @phpstan-ignore-next-line */
                        $referenceCollection->id, //holy luck batman this is nice!
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

            $results = LlmDriverFacade::driver($report->getDriver())
                ->completionPool($prompts);

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

    }

    protected function poolPrompt(array $prompts, Report $report, Document $document): void
    {
        $results = LlmDriverFacade::driver($report->getDriver())
            ->completionPool($prompts);
        foreach ($results as $resultIndex => $result) {
            //make the sections per the results coming back.
            $content = $result->content;
            $this->makeSectionFromContent($content, $document, $report);
        }
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

    /**
     * @return PropertyDto[]
     */
    protected function getProperties(): array
    {
        return [
            new PropertyDto(
                name: 'prompt',
                description: 'The prompt the user is using to use as solutions to the report',
                type: 'string',
                required: true,
            ),
        ];
    }

    public function runAsBatch() : bool {
        return true;
    }
}
