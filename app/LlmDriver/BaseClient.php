<?php

namespace App\LlmDriver;

use App\LlmDriver\Requests\MessageInDto;
use App\LlmDriver\Responses\CompletionResponse;
use App\LlmDriver\Responses\EmbeddingsResponseDto;
use Illuminate\Support\Facades\Log;

abstract class BaseClient
{
    protected string $driver = 'mock';

    public function embedData(string $data): EmbeddingsResponseDto
    {
        if (! app()->environment('testing')) {
            sleep(2);
        }
        Log::info('LlmDriver::MockClient::embedData');

        $data = get_fixture('embedding_response.json');

        return EmbeddingsResponseDto::from([
            'embedding' => data_get($data, 'data.0.embedding'),
            'token_count' => 1000,
        ]);
    }

    /**
     * @param  MessageInDto[]  $messages
     */
    public function chat(array $messages): CompletionResponse
    {
        if (! app()->environment('testing')) {
            sleep(2);
        }

        Log::info('LlmDriver::MockClient::completion');

        $data = <<<'EOD'
        Voluptate irure cillum dolor anim officia reprehenderit dolor. Eiusmod veniam nostrud consectetur incididunt proident id. Anim adipisicing pariatur amet duis Lorem sunt veniam veniam est. Deserunt ea aliquip cillum pariatur consectetur. Dolor in reprehenderit adipisicing consectetur cupidatat ad cupidatat reprehenderit. Nostrud mollit voluptate aliqua anim pariatur excepteur eiusmod velit quis exercitation tempor quis excepteur.        
EOD;

        return new CompletionResponse($data);
    }

    public function completion(string $prompt): CompletionResponse
    {
        if (! app()->environment('testing')) {
            sleep(2);
        }

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


    public function getFunctions() : array {
        return [];
    }
}
