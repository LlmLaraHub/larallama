<?php

namespace App\Events;

use App\Models\Report;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ReportingEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public Report $report,
        public string $updateMessage
    ) {
        //
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel(
                'collection.chat.reports.'.$this->report->id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'update';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->report->id,
            /** @phpstan-ignore-next-line */
            'status_sections_generation' => $this->report->status_sections_generation?->value,
            /** @phpstan-ignore-next-line */
            'status_entries_generation' => $this->report->status_entries_generation?->value,
            'updateMessage' => $this->updateMessage,
        ];
    }
}
