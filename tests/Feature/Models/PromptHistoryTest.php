<?php

namespace Tests\Feature\Models;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class PromptHistoryTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_model(): void
    {
        $model = \App\Models\PromptHistory::factory()->create();

        $this->assertNotNull($model->chat->id);
        $this->assertNotNull($model->collection->id);
        $this->assertNotNull($model->collection->prompt_history);
        $this->assertNotNull($model->message->id);

    }
}
