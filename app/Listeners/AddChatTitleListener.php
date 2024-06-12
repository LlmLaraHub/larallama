<?php

namespace App\Listeners;

use App\Events\MessageCreatedEvent;
use Facades\App\Domains\Chat\TitleRepo;
use Illuminate\Contracts\Queue\ShouldQueue;

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
        notify_ui($event->message->chat, 'Chat Title Updated');
    }
}
