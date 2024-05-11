<?php

namespace Tests\Feature\Models;

use App\Models\Output;
use Tests\TestCase;

class OutputTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_model(): void
    {
        $output = Output::factory()->create();

        $this->assertNotNull($output->slug);
        $this->assertNotNull($output->collection->id);
        $this->assertNotNull($output->collection->outputs()->first()->id);
    }
}
