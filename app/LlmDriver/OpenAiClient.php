<?php 

namespace App\LlmDriver;

use App\LlmDriver\Responses\CompletionResponse;
use App\LlmDriver\Responses\EmbeddingsResponseDto;
use Illuminate\Support\Facades\Log;
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
            $embedding->object; // 'embedding'
            $results = $embedding->embedding; // [0.018990106880664825, -0.0073809814639389515, ...]
            $embedding->index; // 0
        }

        return new EmbeddingsResponseDto(
            $results,
            $response->usage->totalTokens,
        );
    }

    public function completion(string $prompt, int $temperature = 0): CompletionResponse
    {
        $response = OpenAI::completions()->create([
            'model' => $this->getConfig('openai')['completion_model'],
            'prompt' => $prompt,
            'temperature' => 0
        ]);

        $results = null;

        foreach ($response->choices as $result) {
            $results = $result->text; // '\n\nThis is a test'
        }

        return new CompletionResponse($results);
    }

}