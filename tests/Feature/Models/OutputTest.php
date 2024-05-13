<?php

namespace Tests\Feature\Models;

use App\Domains\Outputs\OutputTypeEnum;
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
        $this->assertNotNull($output->meta_data);
        $this->assertEquals(OutputTypeEnum::WebPage, $output->type);
        $this->assertNotNull($output->collection->id);
        $this->assertNotNull($output->collection->outputs()->first()->id);
    }
}
