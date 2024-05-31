<?php

namespace Tests\Feature;

use Mockery;
use Tests\TestCase;
use App\Models\Document;
use App\Models\Transformer;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use App\Transformer\TransformerEnum;
use App\Transformer\Types\[RESOURCE_CLASS_NAME];

class [RESOURCE_CLASS_NAME]Test extends TestCase
{
    use SharedSetupForPdfFile;

    public function test_parses()
    {
        $document = Document::factory()->html()->create();

        $transformerModel = Transformer::factory()->create([
            'type' => TransformerEnum::[RESOURCE_CLASS_NAME],
        ]);

        Storage::fake('projects');

        $transformer = new [RESOURCE_CLASS_NAME]($document);
        $this->assertDatabaseCount('document_chunks', 0);
        $transformer->handle($transformerModel);
        $this->assertDatabaseCount('document_chunks', 1);

        $document = Document::first();
        $content = $document->content;

        $this->assertNotNull($content);

    }

}
