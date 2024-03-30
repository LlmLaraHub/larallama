<?php

namespace App\LlmDriver\Functions;

class SearchAndSummarize extends FunctionContract
{
    protected string $name = 'search_and_summarize';

    protected string $dscription = 'Used to embed users prompt, search database and return summarized results.';

    public function handle(FunctionCallDto $functionCallDto): array
    {

        return [];
    }

    /**
     * @return ParameterDto[]
     */
    protected function getParameters(): array
    {
        return [
            new ParameterDto(
                name: 'prompt',
                description: 'The prompt to search for in the database.',
                type: 'string',
                required: true,
            ),
        ];
    }
}
