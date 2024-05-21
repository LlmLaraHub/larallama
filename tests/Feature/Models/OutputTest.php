<?php

namespace Tests\Feature\Models;

use App\Domains\Outputs\OutputTypeEnum;
use App\Domains\Recurring\RecurringTypeEnum;
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

    public function test_meta_data()
    {
        $output = Output::factory()->create([
            'recurring' => RecurringTypeEnum::HalfHour,
            'meta_data' => [
                'to' => 'bob@bobsburgers.com',
            ],
        ]);

        $this->assertEquals('bob@bobsburgers.com',
            $output->fromMetaData('to'));

        $output = Output::factory()->create([
            'recurring' => RecurringTypeEnum::HalfHour,
            'meta_data' => [],
        ]);

        $this->assertNotNull($output->fromMetaData('to'));
    }
}
