<?php

namespace App\Jobs;

use App\Domains\Sources\WebSearch\Response\WebResponseDto;
use App\Models\Source;
use Illuminate\Bus\Batch;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Bus;
use LlmLaraHub\LlmDriver\LlmDriverFacade;

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

        $title = sprintf('WebPageSource - source: %s', $this->url);

        $webResponseDto = WebResponseDto::from([
            'url' => $this->url,
            'title' => $title,
            'age' => now()->toString(),
            'description' => sprintf('From Source %s', $this->source->title),
            'meta_data' => [],
            'thumbnail' => null,
            'profile' => [],
        ]);

        Bus::batch([
            new GetWebContentJob($this->source, $webResponseDto),
        ])
            ->name("Getting Web content for Source - {$this->url}")
            ->onQueue(LlmDriverFacade::driver($this->source->getDriver())->onQueue())
            ->allowFailures()
            ->dispatch();

    }
}
