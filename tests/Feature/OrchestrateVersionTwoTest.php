<?php

namespace Tests\Feature;

use App\Models\Chat;
use App\Models\Collection;
use App\Models\Message;
use Illuminate\Support\Facades\Bus;
use LlmLaraHub\LlmDriver\LlmDriverFacade;
use LlmLaraHub\LlmDriver\Responses\OllamaChatCompletionResponse;
use Tests\TestCase;

class OrchestrateVersionTwoTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_batches(): void
    {
        Bus::fake();

        $data = get_fixture('ollama_response_tools.json');

        LlmDriverFacade::shouldReceive('driver->setToolType->chat')->once()->andReturn(
            OllamaChatCompletionResponse::from($data)
        );

        $prompt = <<<'PROMPT'
Get the content from the url https://dailyai.studio

Then do a summary of the content breaking it down into
three points that would make sense to a real-estate agent

PROMPT;

        $chat = Chat::factory()->create();

        $collection = Collection::factory()->create([
            'team_id' => $chat->chatable->team_id,
            'driver' => 'ollama',
            'embedding_driver' => 'ollama',
        ]);

        $message = Message::factory()->create([
            'chat_id' => $chat->id,
            'body' => $prompt,
        ]);

        (new \App\Domains\Orchestration\OrchestrateVersionTwo())
            ->handle($chat, $message);

        //test it made one message for the tool request
        //test it made one message for the results of the url
        //and one message for the prompt

        Bus::assertBatchCount(1);

    }
}
