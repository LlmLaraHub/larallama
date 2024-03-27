<?php

namespace App\LlmDriver;

use App\LlmDriver\Responses\CompletionResponse;
use App\LlmDriver\Responses\EmbeddingsResponseDto;
use OpenAI\Laravel\Facades\OpenAI;

class OpenAiClient extends BaseClient
{
    protected string $driver = 'openai';

    public function embedData(string $data): EmbeddingsResponseDto
    {

        $response = OpenAI::embeddings()->create([
            'model' => $this->getConfig('openai')['embedding_model'],
            'input' => $data,
        ]);

        $results = [];

        foreach ($response->embeddings as $embedding) {
            $results = $embedding->embedding; // [0.018990106880664825, -0.0073809814639389515, ...]
        }

        return new EmbeddingsResponseDto(
            $results,
            $response->usage->totalTokens,
        );
    }

    public function completion(string $prompt, int $temperature = 0): CompletionResponse
    {
        $response = OpenAI::chat()->create([
            'model' => $this->getConfig('openai')['completion_model'],
            'messages' => [
                ['role' => 'user', 'content' => $prompt],
            ],
        ]);

        $results = null;

        foreach ($response->choices as $result) {
            $results = $result->message->content;
        }

        return new CompletionResponse($results);
    }
}
