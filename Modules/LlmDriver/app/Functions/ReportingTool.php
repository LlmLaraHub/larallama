<?php

namespace LlmLaraHub\LlmDriver\Functions;

use App\Domains\Prompts\ReportBuildingFindRequirementsPrompt;
use App\Domains\Prompts\StandardsCheckerPrompt;
use App\Domains\Reporting\ReportTypeEnum;
use App\Models\Message;
use App\Models\Report;
use App\Models\Section;
use Illuminate\Support\Facades\Log;
use LlmLaraHub\LlmDriver\LlmDriverFacade;
use LlmLaraHub\LlmDriver\Requests\MessageInDto;
use LlmLaraHub\LlmDriver\Responses\FunctionResponse;

class ReportingTool extends FunctionContract
{
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
            'reference_collection_id' => $message->getReferenceCollection()?->id,
            'user_id' => $message->getChat()->user_id,
        ], [
            'type' => ReportTypeEnum::RFP,
        ]);


        $documents = $message->getChatable()->documents;

        notify_ui($message->getChat(), 'Going through all the documents to check requirements');

        $this->results = [];

        foreach($documents->chunk(3) as $index => $databaseChunk) {
            try {
                $prompts = [];
                $documents = [];

                foreach ($databaseChunk as $document) {
                    $documents[] = $document;
                    $content = $document->document_chunks->pluck('content')->toArray();

                    $content = implode("\n", $content);

                    /**
                     * @NOTE
                     * This assumes a small amount of incoming content to check
                     * The user my upload a blog post that is 20 paragraphs or more.
                     */
                    $prompt = ReportBuildingFindRequirementsPrompt::prompt(
                        $content, $message->getContent(), $message->getChatable()->description
                    );
                    $this->promptHistory[] = $prompt;
                    $prompts[] = $prompt;

                }

                $results = LlmDriverFacade::driver($message->getDriver())
                    ->completionPool($prompts);

                foreach ($results as $result) {
                    //make the sections per the results coming back.
                    $content = $result->content;
                    $content = json_decode($content, true);
                    foreach($content as $sectionIndex =>$sectionText) {
                        $title = data_get($sectionText, 'title', "NOT TITLE GIVEN");
                        $content = data_get($sectionText, 'content', "NOT CONTENT GIVEN");

                        $section = Section::updateOrCreate([
                            'document_id' => $document->id,
                            'report_id' => $report->id,
                            'sort_order' => $sectionIndex,
                            ],[
                                'subject' => $title,
                                'content' => $content,
                        ]);
                    }

                    $this->results[] = $section->content;

                }

            } catch (\Exception $e) {
                Log::error('Error running Reporting Tool Checker', [
                    'error' => $e->getMessage(),
                    'index' => $index,
                ]);
            }
        }

        notify_ui($message->getChat(), 'Wow that was a lot of document!');

        return FunctionResponse::from([
            'content' => implode('\n', $this->results),
            'prompt' => implode('\n', $this->promptHistory),
            'requires_followup' => false,
            'documentChunks' => collect([]),
        ]);
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
