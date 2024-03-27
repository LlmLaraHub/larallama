<?php

namespace Tests\Feature\Models;

use Tests\TestCase;

class CollectionTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_factory(): void
    {
        $model = \App\Models\Collection::factory()->create();

        $this->assertNotNull($model->team->id);

    }

    public function test_system_prompt(): void
    {
        $model = \App\Models\Collection::factory()->create();

        $this->assertStringContainsString(
            config('llmlarahub.collection.system_prompt'),
            $model->systemPrompt());

        $this->assertStringContainsString(
            $model->description,
            $model->systemPrompt());
    }
}
