<?php

namespace App\Jobs;

use App\Domains\Documents\StatusEnum;
use App\Domains\Documents\TypesEnum;
use App\Models\Document;
use App\Models\Source;
use Facades\App\Domains\Sources\WebSearch\GetPage;
use Illuminate\Bus\Batch;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class WebPageSourceJob implements ShouldQueue
{
    use Batchable;
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    use WebHelperTrait;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Source $source,
        public string $url
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if ($this->batch()->cancelled()) {
            // Determine if the batch has been cancelled...

            return;
        }

        $jobs = [];

        $html = GetPage::make($this->source->collection)->handle($this->url);

        $html = GetPage::parseHtml($html);

        $html = to_utf8($html);

        $title = sprintf('WebPageSource - source: %s', $this->url);

        $parseTitle = str($html)->limit(50)->toString();

        if (! empty($parseTitle)) {
            $title = $parseTitle;
        }

        $document = Document::updateOrCreate(
            [
                'source_id' => $this->source->id,
                'link' => $this->url,
                'collection_id' => $this->source->collection_id,
            ],
            [
                'status' => StatusEnum::Pending,
                'type' => TypesEnum::HTML,
                'subject' => to_utf8($title),
                'file_path' => $this->url,
                'summary' => str($html)->limit(254)->toString(),
                'status_summary' => StatusEnum::Pending,
                'original_content' => $html,
                'meta_data' => $this->source->meta_data,
            ]
        );

        $this->processDocument($document);

    }
}
