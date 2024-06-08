<?php

namespace App\Listeners;

use Facades\App\Domains\Chat\TitleRepo;
use App\Events\MessageCreatedEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class AddChatTitleListener implements ShouldQueue
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
    public function handle(MessageCreatedEvent $event): void
    {
        TitleRepo::handle($event->message);
    }
}
