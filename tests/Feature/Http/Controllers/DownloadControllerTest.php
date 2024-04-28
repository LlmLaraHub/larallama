<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Collection;
use App\Models\Document;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DownloadControllerTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_download(): void
    {
        $this->markTestSkipped('@TODO need to mock better');
        Storage::fake('collections');
        $user = $this->createUserWithCurrentTeam();
        $collection = Collection::factory()->create();
        $document = Document::factory()->create([
            'collection_id' => $collection->id,
            'file_path' => 'test.pdf',
        ]);
        $this->actingAs($user)->get(
            route('download.document', ['collection' => $collection,
                'document_name' => $document->file_path])
        )->assertStatus(200);
    }
}
