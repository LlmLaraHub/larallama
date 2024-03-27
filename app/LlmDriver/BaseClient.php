<?php

namespace App\LlmDriver;

use App\LlmDriver\Responses\CompletionResponse;
use App\LlmDriver\Responses\EmbeddingsResponseDto;
use Illuminate\Support\Facades\Log;

abstract class BaseClient
{
    protected string $driver = 'mock';

    public function embedData(string $data): EmbeddingsResponseDto
    {

        Log::info('LlmDriver::MockClient::embedData');

        $data = get_fixture('embedding_response.json');

        return new EmbeddingsResponseDto(
            data_get($data, 'data.0.embedding'),
            1000,
        );
    }

    public function completion(string $prompt): CompletionResponse
    {
        Log::info('LlmDriver::MockClient::completion');

        $data = <<<'EOD'
        Voluptate irure cillum dolor anim officia reprehenderit dolor. Eiusmod veniam nostrud consectetur incididunt proident id. Anim adipisicing pariatur amet duis Lorem sunt veniam veniam est. Deserunt ea aliquip cillum pariatur consectetur. Dolor in reprehenderit adipisicing consectetur cupidatat ad cupidatat reprehenderit. Nostrud mollit voluptate aliqua anim pariatur excepteur eiusmod velit quis exercitation tempor quis excepteur.        
EOD;

        return new CompletionResponse($data);
    }

    protected function getConfig(string $driver): array
    {
        return config("llmdriver.drivers.$driver");
    }
}
