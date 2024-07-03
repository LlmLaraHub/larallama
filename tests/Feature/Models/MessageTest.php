<?php

namespace Tests\Feature\Models;

use App\Domains\Chat\MetaDataDto;
use App\Models\Message;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
        $this->assertInstanceOf(MetaDataDto::class, $model->meta_data);
        $this->assertNotNull($model->meta_data->date_range);
    }
}
