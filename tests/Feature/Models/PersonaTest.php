<?php

namespace Tests\Feature\Models;

use Tests\TestCase;

class PersonaTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_model(): void
    {
        $model = \App\Models\Persona::factory()->create();
        $this->assertNotNull($model->name);
    }

    public function test_in_persona_wrapped(): void
    {
        $input = 'Foo bar';
        $model = \App\Models\Persona::factory()->create();
        $results = $model->wrapPromptInPersona($input);
        $this->assertStringContainsString($input, $results);
        $this->assertStringContainsString($model->name, $results);
        $this->assertStringContainsString($model->content, $results);
    }
}
