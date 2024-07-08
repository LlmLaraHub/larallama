<?php

namespace Tests\Feature;

use App\Domains\Documents\TypesEnum;
use Tests\TestCase;

class TypesEnumTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_gets_mime_type_to_type(): void
    {
        $results = TypesEnum::mimeTypeToType(
            'application/vnd.openxmlformats-officedocument.presentationml.presentation');

        $this->assertEquals(TypesEnum::Pptx, $results);
    }

    public function test_csv(): void
    {
        $results = TypesEnum::mimeTypeToType(
            'text/csv');

        $this->assertEquals(TypesEnum::CSV, $results);
    }

    public function test_text_plain(): void
    {
        $results = TypesEnum::mimeTypeToType(
            'text/plain');

        $this->assertEquals(TypesEnum::Txt, $results);
    }

    public function test_docx(): void
    {
        $results = TypesEnum::mimeTypeToType(
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document');

        $this->assertEquals(TypesEnum::Docx, $results);
    }
}
