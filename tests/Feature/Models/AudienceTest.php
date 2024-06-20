<?php

namespace Tests\Feature\Models;

use Tests\TestCase;

class AudienceTest extends TestCase
{
    public function test_model(): void
    {
        $model = \App\Models\Audience::factory()->create();
        $this->assertNotNull($model->name);
    }

    public function test_in_persona_wrapped(): void
    {
        $input = 'Foo bar';
        $model = \App\Models\Audience::factory()->create();
        $results = $model->wrapPromptInAudience($input);
        $this->assertStringContainsString($input, $results);
        $this->assertStringContainsString($model->name, $results);
        $this->assertStringContainsString($model->content, $results);
    }
}
