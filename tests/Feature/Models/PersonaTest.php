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
}
