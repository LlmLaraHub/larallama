<?php

namespace LlmLaraHub\LlmDriver\Functions;

use App\Domains\Chat\UiStatusEnum;
use App\Domains\Messages\RoleEnum;
use App\Domains\Prompts\ReportBuildingFindRequirementsPrompt;
use App\Domains\Prompts\ReportingSummaryPrompt;
use App\Domains\Reporting\ReportTypeEnum;
use App\Domains\Reporting\StatusEnum;
use App\Events\ReportingEvent;
use App\Jobs\MakeReportSectionsJob;
use App\Jobs\ReportMakeEntriesJob;
use App\Models\Message;
use App\Models\Report;
use Illuminate\Bus\Batch;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Bus;
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

    protected array $sectionJobs = [];

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

        $this->buildUpSections($documents, $report, $message);

        Bus::batch($this->sectionJobs)
            ->name(sprintf('Reporting Tool Sections Report id: %d Chat id %d', $report->id, $message->getChat()->id))
            ->allowFailures()
            ->finally(function (Batch $batch) use ($report) {
                $report->update(['status_sections_generation' => StatusEnum::Complete]);

                Bus::batch([
                    new ReportMakeEntriesJob($report),
                ])->name(sprintf('Reporting Entities Report Id %s', $report->id))
                    ->allowFailures()
                    ->finally(function (Batch $batch) use ($report) {
                        $report->update([
                            'status_entries_generation' => StatusEnum::Complete,
                        ]);
                        ReportingEvent::dispatch(
                            $report,
                            UiStatusEnum::Complete->name
                        );
                    })
                    ->dispatch();

            })
            ->dispatch();

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

        notify_ui($message->getChat(), 'Building Solutions list');
        notify_ui_report($report, 'Building Solutions list');

        return FunctionResponse::from([
            'content' => $response->content,
            'prompt' => implode('\n', $this->promptHistory),
            'requires_followup' => false,
            'documentChunks' => collect([]),
            'save_to_message' => false,
        ]);
    }

    protected function buildUpSections(Collection $documents, Report $report, Message $message): void
    {
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

                    $this->sectionJobs[] = new MakeReportSectionsJob($prompts, $report, $document);
                }

            } catch (\Exception $e) {
                Log::error('Error running Reporting Tool Checker', [
                    'error' => $e->getMessage(),
                    'index' => $index,
                    'line' => $e->getLine(),
                ]);
            }
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

    public function runAsBatch(): bool
    {
        return true;
    }
}
