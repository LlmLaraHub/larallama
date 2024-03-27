<?php

namespace Tests\Feature\Models;

use App\Models\Chat;
use App\Models\Collection;
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
        $collection = Collection::factory()->create();
        $model = Chat::factory()->create([
            'chatable_id' => $collection->id,
        ]);
        $this->assertNotNull($model->user_id);
        $this->assertNotNull($model->user->id);
        $this->assertNotNull($model->chatable_id);
        $this->assertNotNull($model->chatable->id);
        $this->assertNotNull($collection->chats()->first()->id);
    }
}
