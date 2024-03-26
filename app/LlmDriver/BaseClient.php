<?php 

namespace App\LlmDriver;

use App\LlmDriver\Responses\EmbeddingsResponseDto;
use Illuminate\Support\Facades\Log;
use OpenAI\Resources\Embeddings;

abstract class BaseClient {
    
    public function embedData(string $data) : EmbeddingsResponseDto {

        Log::info("LlmDriver::MockClient::embedData");
        
        $data = get_fixture('embedding_response.json');

        return new EmbeddingsResponseDto(
            data_get($data, 'data.0.embedding'),
            1000,
        );
    }

}