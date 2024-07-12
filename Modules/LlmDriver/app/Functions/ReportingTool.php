<?php

namespace LlmLaraHub\LlmDriver\Functions;

use App\Domains\Messages\RoleEnum;
use App\Domains\Prompts\ReportBuildingFindRequirementsPrompt;
use App\Domains\Prompts\ReportingSummaryPrompt;
use App\Domains\Reporting\ReportTypeEnum;
use App\Models\Document;
use App\Models\Message;
use App\Models\Report;
use App\Models\Section;
use Illuminate\Support\Facades\Log;
use LlmLaraHub\LlmDriver\LlmDriverFacade;
use LlmLaraHub\LlmDriver\Responses\CompletionResponse;
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

        //make or update a reports table for this message - chat
        //gather all the documents
        //and for each document
        //GOT TO TRIGGER THE PROMPT TO BE RELATIVE
        //  like a summary of the goal or something
        //build up a list of sections that are requests (since this is a flexible tool that will be part of a prompt
        //then save each one with a reference to the document, chunk to the sections table
        //then for each section review each related collections solutions to make numerous
        // or use vector search
        //entries to address the sections requirements
        //saving to the entries the related collection, document, document_chunk, full text (siblings)
        //then build a response for each section to the response field of the section.
        //finally build up a summary of all the responses for the report
        //this will lead to a ui to comment on "sections" and "responses"

        $report = Report::firstOrCreate([
            'chat_id' => $message->getChat()->id,
            'message_id' => $message->id,
            'reference_collection_id' => $message->getReferenceCollection()?->id,
            'user_id' => $message->getChat()->user_id,
        ], [
            'type' => ReportTypeEnum::RFP,
        ]);

        $documents = $message->getChatable()->documents;

        notify_ui($message->getChat(), 'Going through all the documents to check requirements');

        $this->results = [];

        $sectionContent = [];

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

        notify_ui($message->getChat(), 'Wow that was a lot of document! Now to finalize the output');

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

        $report->message_id = $assistantMessage->id;
        $report->save();

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
            }
        } catch (\Exception $e) {
            Log::error('Error parsing JSON', [
                'error' => $e->getMessage(),
                'content' => $content,
                'line' => $e->getLine(),
            ]);
        }
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
}
