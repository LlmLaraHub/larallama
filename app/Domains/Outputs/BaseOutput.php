<?php

namespace App\Domains\Outputs;

use App\Jobs\SendOutputEmailJob;
use App\Models\Output;
use Illuminate\Support\Facades\Log;

abstract class BaseOutput
{
    public function getContext(Output $output): array
    {
        $content = [];

        $documents = $output->collection
            ->documents()
            ->when($output->last_run != null, function ($query) use ($output) {
                $query->whereDate('created_at', '>=', $output->last_run);
            }, function ($query) {
                $query->limit(5);
            })
            ->latest()
            ->get();

        if ($documents->count() === 0) {
            Log::info('LaraChain] - No Emails since the last run');

            return $content;
        }

        foreach ($documents as $document) {
            $divisor = too_large_for_json($document->document_chunks()->count());

            Log::info('[LaraChain] Divisor', [
                'count' => $divisor,
            ]);

            foreach ($document->document_chunks as $chunkIndex => $chunk) {
                if ($chunkIndex % $divisor == 0) {
                    $content[] = $chunk->content;
                }
            }
        }

        return $content;
    }

    public function handle(Output $output): void
    {
        /**
         * So many output types
         * Email
         * API
         * Fax :)
         * Webhooks
         */
        SendOutputEmailJob::dispatch($output);
    }
}
