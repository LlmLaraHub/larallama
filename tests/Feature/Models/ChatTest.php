<?php

namespace Tests\Feature\Models;

use App\Models\Chat;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChatTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic feature test example.
     */
    public function test_factory(): void
    {
        $model = Chat::factory()->make();
        $this->assertIsString($model->title);
    }
}
