<?php

namespace App\LlmDriver\Functions;

class SearchAndSummarize extends FunctionContract
{
    protected string $name = 'search_and_summarize';

    protected string $description = 'Used to embed users prompt, search database and return summarized results.';

    public function handle(FunctionCallDto $functionCallDto): array
    {
        return [];
    }

    /**
     * @return PropertyDto[]
     */
    protected function getProperties(): array
    {
        return [
            new PropertyDto(
                name: 'prompt',
                description: 'The prompt the user is using the search for.',
                type: 'string',
                required: true,
            ),
        ];
    }
}
