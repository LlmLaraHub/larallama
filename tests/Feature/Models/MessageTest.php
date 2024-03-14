<?php

namespace Tests\Feature\Models;

use App\Models\Chat;
use App\Models\Message;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class MessageTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     */
    public function test_factory(): void
    {
        $model = Message::factory()->create();
        $this->assertNotNull($model->body);
        $this->assertNotNull($model->chat->id);
    }
}
