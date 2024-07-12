<?php

namespace Tests\Feature\Models;

use Tests\TestCase;

class EntryTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_model(): void
    {
        $model = \App\Models\Entry::factory()->create();

        $this->assertNotNull($model->section->id);
        $this->assertNotNull($model->document->id);
        $this->assertInstanceOf(\App\Domains\Reporting\EntryTypeEnum::class, $model->type);

    }
}
