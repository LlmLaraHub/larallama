<?php

namespace LlmLaraHub\LlmDriver\Functions;

use App\Domains\Reporting\ReportTypeEnum;
use App\Domains\Reporting\StatusEnum;
use App\Jobs\GatherInfoFinalPromptJob;
use App\Jobs\GatherInfoReportSectionsJob;
use App\Models\Collection;
use App\Models\Message;
use App\Models\Report;
use Facades\App\Domains\Tokenizer\Templatizer;
use Illuminate\Bus\Batch;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;
use LlmLaraHub\LlmDriver\HasDrivers;
use LlmLaraHub\LlmDriver\Responses\FunctionResponse;
use LlmLaraHub\LlmDriver\ToolsHelper;

class GatherInfoTool extends FunctionContract
{
    use ToolsHelper;

    public array $toolTypes = [
        ToolTypes::ManualChoice,
    ];

    protected string $name = 'gather_info_tool';

    protected string $description = 'This will look at all documents using your prompt then return the results after once more using your prompt. This is great for say Find all events from my collection of data and build a list of Event Dates, Titles Grouped by Month';

    protected string $response = '';

    protected array $results = [];

    protected array $sectionJobs = [];

    public function handle(
        Message $message): FunctionResponse
    {
        Log::info('[LaraChain] GatherInfoTool Function called');

        $report = Report::firstOrCreate([
            'chat_id' => $message->getChat()->id,
            'message_id' => $message->id,
            'reference_collection_id' => $message->getReferenceCollection()?->id,
            'user_id' => $message->getChat()->user_id,
        ], [
            'type' => ReportTypeEnum::GatherInfo,
            'user_message_id' => $message->id,
            'status_sections_generation' => StatusEnum::Pending,
            'status_entries_generation' => StatusEnum::Pending,
        ]);

        $collection = $message->getChatable();

        notify_ui($message->getChat(), 'Going through all the documents to check requirements');

        $this->results = [];

        Log::info('[LaraChain] - GatherInfo Tool');

        $this->buildUpSections($collection, $report, $message);

        Bus::batch($this->sectionJobs)
            ->name(sprintf('GatherInfo Tool Sections Report id: %d Chat id %d', $report->id, $message->getChat()->id))
            ->allowFailures()
            ->finally(function (Batch $batch) use ($report) {
                $report->update(['status_sections_generation' => StatusEnum::Complete]);
                Bus::batch([
                    new GatherInfoFinalPromptJob($report),
                ])->name(sprintf('Reporting Tool Summarize Report Id %s', $report->id))
                    ->allowFailures()
                    ->dispatch();

            })
            ->dispatch();

        $report->update([
            'status_sections_generation' => StatusEnum::Running,
        ]);

        notify_ui($report->getChat(), 'Running');

        return FunctionResponse::from([
            'content' => 'Gathering info and then running prompt',
            'prompt' => $message->getPrompt(),
            'requires_followup' => false,
            'documentChunks' => collect([]),
            'save_to_message' => false,
        ]);
    }

    protected function buildUpSections(Collection|HasDrivers $collection, Report $report, Message $message): void
    {
        $messagePrompt = $message->getPrompt();
        $collection->documents()->chunk(3, callback: function ($documentChunks) use ($report, $messagePrompt) {
            try {

                $prompts = [];
                foreach ($documentChunks as $document) {
                    $prompt = Templatizer::appendContext(true)
                        ->handle($messagePrompt, $document->original_content);
                    $prompts[] = $prompt;
                }

                if ($document?->id) {
                    $this->sectionJobs[] =
                        new GatherInfoReportSectionsJob(
                            prompts: $prompts,
                            report: $report,
                            document: $document);
                }

            } catch (\Exception $e) {
                Log::error('Error running Reporting Tool Checker', [
                    'error' => $e->getMessage(),
                    'line' => $e->getLine(),
                ]);
            }
        });
    }

    /**
     * @return PropertyDto[]
     */
    protected function getProperties(): array
    {
        return [
            new PropertyDto(
                name: 'prompt',
                description: 'Using your prompt we will look at every document, run your prompt against each one and then against the final output',
                type: 'string',
                required: true,
            ),
        ];
    }

    public function runAsBatch(): bool
    {
        return true;
    }
}
