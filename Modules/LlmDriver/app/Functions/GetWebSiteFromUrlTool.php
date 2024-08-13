<?php

namespace LlmLaraHub\LlmDriver\Functions;

use App\Models\Message;
use Facades\App\Domains\Sources\WebSearch\GetPage;
use Illuminate\Support\Facades\Log;
use LlmLaraHub\LlmDriver\Responses\FunctionResponse;
use LlmLaraHub\LlmDriver\ToolsHelper;

class GetWebSiteFromUrlTool extends FunctionContract
{
    use ToolsHelper;

    public bool $showInUi = false;

    public array $toolTypes = [
        ToolTypes::ManualChoice,
        ToolTypes::Source,
        ToolTypes::Output,
    ];

    protected string $name = 'get_web_site_from_url';

    protected string $description = 'If you add urls to a prompt and ask the llm to get the web site using the url(s) you give it';

    public function handle(
        Message $message): FunctionResponse
    {
        Log::info('[LaraChain] GetWebSiteFromUrlTool called');

        $args = $message->meta_data->args;

        $url = data_get($args, 'url', null);

        if (! $url) {
            throw new \Exception('No url found');
        }

        Log::info('[LaraChain] GetWebSiteFromUrlTool called', [
            'url' => $url,
        ]);

        $results = GetPage::handle($url);

        $results = <<<CONTENT
        Title: $results->title
        Description: $results->description
        URL: $results->url
        Content:
        $results->content
        CONTENT;

        return FunctionResponse::from([
            'content' => $results,
            'prompt' => $message->getContent(),
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
                name: 'url',
                description: 'The URL To get',
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
