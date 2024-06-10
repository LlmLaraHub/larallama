<?php

namespace Tests\Feature;

use App\Domains\Sources\SourceTypeEnum;
use App\Models\Source;
use Tests\TestCase;

class JsonSourceTest extends TestCase
{
    public function test_run()
    {
        $this->markTestSkipped('@TODO not sure this class is needed yet or at all');
        $source = Source::factory()->create([
            'slug' => 'test',
            'type' => SourceTypeEnum::JsonSource,
        ]);

        $source->run();
    }
}
