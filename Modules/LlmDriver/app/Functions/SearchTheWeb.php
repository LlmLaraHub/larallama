<?php

namespace LlmLaraHub\LlmDriver\Functions;

use App\Domains\Sources\WebSearch\Response\SearchResponseDto;
use App\Domains\Sources\WebSearch\WebSearchFacade;
use App\Domains\WebParser\WebContentResultsDto;
use App\Helpers\ChatHelperTrait;
use App\Models\Message;
use Facades\App\Domains\Sources\WebSearch\GetPage;
use Illuminate\Support\Facades\Log;
use LlmLaraHub\LlmDriver\LlmDriverFacade;
use LlmLaraHub\LlmDriver\Responses\FunctionResponse;
use LlmLaraHub\LlmDriver\ToolsHelper;

class SearchTheWeb extends FunctionContract
{
    use ChatHelperTrait, ToolsHelper;

    public bool $showInUi = false;

    public array $toolTypes = [
        ToolTypes::ManualChoice,
        ToolTypes::Source,
        ToolTypes::Output,
    ];

    protected string $name = 'search_the_web';

    protected string $description = 'Search the web for a topic ONLY if the user asks for web search';

    public function handle(
        Message $message): FunctionResponse
    {
        Log::info('[LaraChain] SearchTheWeb called');

        $args = $message->meta_data->args;

        $search = data_get($args, 'search_phrase', null);

        if (! $search) {
            throw new \Exception('No search_phrase');
        }

        $driver = config('llmdriver.sources.search_driver');

        /** @var SearchResponseDto $response */
        $response = WebSearchFacade::driver($driver)->search(
            search: $search,
            options: [
                'limit' => 3,
            ]
        );

        Log::info('[LaraChain] SearchTheWeb found results now processing');

        $html = [];
        $prompt = '';
        foreach ($response->getWeb() as $web) {
            /** @var WebContentResultsDto $results */
            $results = GetPage::handle($web->url);
            $resultsContent = $results->content;
            $prompt = <<<PROMPT
Web URL: {$web->url}

{$message->getPrompt()}

<RESULTS OF WEB SEARCH ARTICLE ONE OF A FIVE>
Title: {$results->title}
Description: {$results->description}
Content:
{$resultsContent}
PROMPT;

            $html[] = $prompt;
        }

        $poolResults = LlmDriverFacade::driver($message->getDriver())
            ->completionPool($html);

        $finalResults = [];
        foreach ($poolResults as $resultIndex => $result) {
            try {
                /**
                 * @NOTE
                 * This is the feature that lets a user ask for false
                 * in a prompt so we ignore it
                 */
                if ($this->ifNotActionRequired($result->content)) {
                    continue;
                } else {
                    /**
                     * @NOTE
                     * This array thingy just allows the user to ask for the
                     * data as an array of objects
                     */
                    $promptResultsOriginal = $result->content;
                    $promptResults = $this->arrifyPromptResults($promptResultsOriginal);
                    foreach ($promptResults as $promptResultIndex => $promptResult) {
                        $finalResults[] = json_encode($promptResult);
                    }
                }
            } catch (\Exception $e) {
                Log::error('Error processing SearchTheWeb', [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $results = 'No results from web search that met the criteria';

        if (! empty($finalResults)) {
            $results = implode("\n", $finalResults);
        }

        return FunctionResponse::from([
            'content' => $results,
            'prompt' => $message->getPrompt(),
            'requires_followup' => false,
            'documentChunks' => collect([]),
            'save_to_message' => false,
        ]);
    }

    /**
     * @return PropertyDto[]
     */
    protected function getProperties(): array
    {
        return [
            new PropertyDto(
                name: 'search_phrase',
                description: '1-5 words to search for',
                type: 'string',
                required: true,
            ),
        ];
    }

    public function runAsBatch(): bool
    {
        return false;
    }
}
