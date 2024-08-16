<?php

namespace Tests\Feature;

use App\Domains\Chat\MetaDataDto;
use App\Models\Message;
use LlmLaraHub\LlmDriver\Functions\CreateEventTool;
use Tests\TestCase;

class CreateEventToolTest extends TestCase
{
    public function test_makes_event(): void
    {
        $data = get_fixture('create_event_tool.json');
        $message = Message::factory()->create([
            'meta_data' => MetaDataDto::from([
                'args' => $data['args'],
            ]),
        ]);
        $this->assertDatabaseCount('events', 0);
        (new CreateEventTool())->handle($message);
        $this->assertDatabaseCount('events', 1);

    }
}
