<?php

namespace App\Http\Controllers;

use App\Domains\Documents\StatusEnum;
use App\Domains\Documents\TypesEnum;
use App\Domains\Collections\CollectionStatusEnum;
use App\Models\Collection;
use App\Models\Document;
use App\Jobs\SummarizeDataJob;
use App\Models\DocumentChunk;
use Illuminate\Bus\Batch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Events\CollectionStatusEvent;
use App\Jobs\SummarizeDocumentJob;
use App\Jobs\VectorlizeDataJob;
use LlmLaraHub\LlmDriver\LlmDriverFacade;

class TextDocumentController extends Controller
{
    public function store(Collection $collection, Request $request)
    {
        $validated = $request->validate([
            'content' => 'required|string',
            'name' => 'required|string',
        ]);

        $document = Document::create([
            'file_path' => $validated['name'],
            'collection_id' => $collection->id,
            'type' => TypesEnum::Txt,
            'summary' => StatusEnum::Pending,
            'status_summary' => StatusEnum::Pending,
        ]);

        $chunks = [];

        $page_number = 0;

        $prompt = <<<EOT
Can you take the following text and break it up into smaller chunks no bigger than 256 characters. The 
size of the chunks should be more about the quality of the content so that when I vectorize the data and save
as document_chunks it will still have some meaning. Please return each chunk in an json array like 
[ "chunk 1", "chunk 2" ] no other surrounding text just json so I can decodd it with PHP: 

### START TEXT TO CHUNK
{$validated['content']}
### END TEXT TO CHUNK
EOT;

        $chunks = LlmDriverFacade::driver($collection->getDriver())
            ->completion($prompt);

        $jobs = [];

        $decoded = json_decode($chunks->content);

        foreach($decoded as $chunk) {
            try {
                $page_number = $page_number + 1;
                $guid = Str::uuid();
                $DocumentChunk = DocumentChunk::updateOrCreate(
                    [
                        'guid' => $guid,
                        'document_id' => $document->id,
                    ],
                    [
                        'content' => $chunk,
                        'sort_order' => $page_number,
                    ]
                );
                /**
                 * Soon taggings
                 * And Summary
                 */
                $jobs[] = [
                    new VectorlizeDataJob($DocumentChunk),
                    new SummarizeDataJob($DocumentChunk),
                    //new TagDataJob($this->document),
                    //then mark it all as done and notify the ui
                ];

                CollectionStatusEvent::dispatch($document->collection, CollectionStatusEnum::PROCESSING);
            } catch (\Exception $e) {
                Log::error('Error parsing PDF', ['error' => $e->getMessage()]);
            }
        }


        Bus::batch($jobs)
            ->name("Chunking Document - $document->file_path")
            ->finally(function (Batch $batch) use ($document) {
                SummarizeDocumentJob::dispatch($document);
            })
            ->allowFailures()
            ->dispatch();

        $request->session()->flash('flash.banner', 'Document created successfully!');
        return back();
    }
}
