<?php

namespace Tests\Feature;

use App\Domains\Transformers\TypeEnum;
use App\Models\Source;
use App\Models\Transformer;
use Tests\TestCase;

class CrmTransformerTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_transformer(): void
    {

        $source = Source::factory()->create();

        $transformer = Transformer::factory()->create([
            'transformable_id' => $source->id,
            'transformable_type' => Source::class,
            'type' => TypeEnum::CrmTransformer,
        ]);

    }
}
