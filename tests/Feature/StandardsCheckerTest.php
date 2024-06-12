<?php

namespace Tests\Feature;

use App\Models\Collection;
use LlmLaraHub\LlmDriver\Functions\ParametersDto;
use LlmLaraHub\LlmDriver\Functions\PropertyDto;
use LlmLaraHub\LlmDriver\Functions\StandardsChecker;
use LlmLaraHub\LlmDriver\LlmDriverFacade;
use LlmLaraHub\LlmDriver\Requests\MessageInDto;
use LlmLaraHub\LlmDriver\Responses\CompletionResponse;
use Tests\TestCase;

class StandardsCheckerTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_can_generate_function_as_array(): void
    {
        $searchAndSummarize = new \LlmLaraHub\LlmDriver\Functions\StandardsChecker();

        $function = $searchAndSummarize->getFunction();

        $parameters = $function->parameters;

        $this->assertInstanceOf(ParametersDto::class, $parameters);
        $this->assertIsArray($parameters->properties);
        $this->assertInstanceOf(PropertyDto::class, $parameters->properties[0]);
    }

    public function test_asks()
    {

        $content = <<<CONTENT
"Compare this content to the standards.
    Example Document

Overview: This document is going to show you how to configure the router and what steps you need to take. It\’s really simple, so just follow along. First, open the admin panel by entering the IP address into your browser. Then you need to go to the settings tab and configure your Wi-Fi settings. Click Save.

After you\’ve done that, you need to check if the configuration is correct. If you get an error, then something went wrong. Check the settings again or maybe restart the router. You should be good to go! Remember, a well-configured router is essential for a strong and reliable internet connection.
    "
CONTENT;

        $messageArray = [];

        $prompt = 'Can you check this document against the standards \n'.$content;

        $messageArray[] = MessageInDto::from([
            'content' => $prompt,
            'role' => 'user',
        ]);

        $dto = CompletionResponse::from([
            'content' => 'Reply 1 2 and 3',
        ]);

        LlmDriverFacade::shouldReceive('driver->completionPool')
            ->times(3)
            ->andReturn([
                $dto,
                $dto,
                $dto,
            ]);

        $collection = Collection::factory()->create();

        $chat = \App\Models\Chat::factory()->create([
            'chatable_type' => Collection::class,
            'chatable_id' => $collection->id,
        ]);

        $document = \App\Models\Document::factory(9)->create([
            'collection_id' => $collection->id,
        ]);

        $functionCallDto = \LlmLaraHub\LlmDriver\Functions\FunctionCallDto::from([
            'function_name' => 'standards_checker',
            'arguments' => json_encode([
                'prompt' => $prompt,
            ]),
        ]);

        $results = (new StandardsChecker())->handle($messageArray, $chat, $functionCallDto);

        $this->assertInstanceOf(\LlmLaraHub\LlmDriver\Responses\FunctionResponse::class, $results);

        $this->assertNotEmpty($results->content);
    }
}
