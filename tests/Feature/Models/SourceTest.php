<?php

namespace Tests\Feature\Models;

use App\Domains\Sources\SourceTypeEnum;
use App\Domains\Sources\WebSearchSource;
use App\Models\Document;
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
        Document::factory(3)->create([
            'source_id' => $model->id,
        ]);

        $this->assertNotNull($model->title);
        $this->assertNotNull($model->collection->id);
        $this->assertNotNull($model->collection->sources()->first()->id);

        $this->assertCount(3, $model->documents);
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
