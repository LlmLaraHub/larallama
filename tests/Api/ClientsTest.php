<?php

namespace Tests\Api;

use App\Domains\Messages\RoleEnum;
use App\Models\Setting;
use App\Models\Source;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use LlmLaraHub\LlmDriver\LlmDriverFacade;
use App\Domains\EmailParser\MailDto;
use LlmLaraHub\LlmDriver\Requests\MessageInDto;
use Tests\TestCase;

class ClientsTest extends TestCase
{


    public function test_clients() {
        $this->markTestSkipped('@TODO will setup the tokens shortly');
        $prompt = <<<PROMPT
What do you know about Laravel
PROMPT;

        $messages = [];
        $messages[] = MessageInDto::from([
            "role" => RoleEnum::User->value,
            "content" => $prompt
        ]);
        $results = LlmDriverFacade::driver("groq")->chat($messages);
        $this->assertNotNull($results->content);
        $results = LlmDriverFacade::driver("openai")->chat($messages);
        $this->assertNotNull($results->content);
        $results = LlmDriverFacade::driver("claude")->chat($messages);
        $this->assertNotNull($results->content);

    }
}
