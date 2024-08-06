<?php

namespace Tests\Feature;

use App\Domains\WebParser\Results\FireCrawResultsDto;
use Tests\TestCase;

class FireCrawResultsDtoTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_dto(): void
    {
        $data = get_fixture('test_firecrawl_parse.json');
        $dto = FireCrawResultsDto::from($data);
        $this->assertEquals('Mendable | AI for CX and Sales', $dto->title);
        $this->assertEquals('AI for CX and Sales', $dto->description);
        $this->assertEquals('# Markdown Content', $dto->content);
        $this->assertEquals('https://www.mendable.ai/', $dto->url);
    }
}
