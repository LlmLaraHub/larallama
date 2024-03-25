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
}
