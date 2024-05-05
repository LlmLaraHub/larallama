<?php

namespace Tests\Feature\Models;

use App\Domains\Sources\SourceTypeEnum;
use App\Domains\Sources\WebSearchSource;
use Mockery;
use Tests\TestCase;

class SourceTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_factory(): void
    {
        $model = \App\Models\Source::factory()->create();

        $this->assertNotNull($model->title);
        $this->assertNotNull($model->collection->id);
        $this->assertNotNull($model->collection->sources()->first()->id);
    }

    public function test_runs_web_search()
    {
        $source = \App\Models\Source::factory()->create([
            'type' => SourceTypeEnum::WebSearchSource,
        ]);
        $this->instance(
            WebSearchSource::class,
            Mockery::mock(WebSearchSource::class, function ($mock) use ($source) {
                $mock->shouldReceive('handle')
                    ->with($source)
                    ->once();
            })
        );

        $source->run();
    }
}
