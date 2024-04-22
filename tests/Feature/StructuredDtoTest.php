<?php

namespace Tests\Feature;

use App\Domains\UnStructured\StructuredDto;
use App\Domains\UnStructured\StructuredTypeEnum;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Nette\Schema\Elements\Structure;
use Tests\TestCase;

class StructuredDtoTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_structured_dto(): void
    {
        $dto = StructuredDto::from([
            'type' => StructuredTypeEnum::Narrative,
            'content' => 'content',
            'title' => 'test title',
            'page' => 'page',
            'guid' => 'guid',
            'file_name' => 'file_name',
            'created_by' => 'Bob Belcher',
            'last_updated_by' => 'Bob Belcher',
            'created_at' => '1713385302',
            'description' => fake()->sentences(3, true),
            'subject' => fake()->sentences(3, true),
            'keywords' => fake()->sentences(3, true),
            'category' => fake()->sentences(3, true),
            'updated_at' => '1713792670',
            'coordinates' => 'coordinates',
            'element_depth' => 'elment_depth',
            'is_continuation' => false,
            'parent_id' => null,
        ]);

        $this->assertNotEmpty($dto->type);
    }
}
