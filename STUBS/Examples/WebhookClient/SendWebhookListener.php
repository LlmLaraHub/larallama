<?php

namespace App\Domains\WebhookClient;

use Facades\App\Domains\WebhookClient\Client;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Statamic\Events\CollectionSaved;
use Statamic\Events\EntrySaved;

class SendWebhookListener
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(EntrySaved $event): void
    {
        Log::info("Sending webhook");
        Client::handle($event->entry->toArray());
        Log::info("Done Sending webhook");
    }
}
