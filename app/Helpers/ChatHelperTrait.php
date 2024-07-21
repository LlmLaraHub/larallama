<?php

namespace App\Helpers;

use App\Models\Chat;
use App\Models\Collection;
use App\Models\Source;

trait ChatHelperTrait
{
    public function checkForChat(Source $source): Source
    {
        if (! $source->chat_id) {
            //@NOTE should I go to Source as the chatable?
            $chat = Chat::create([
                'chatable_id' => $source->collection_id,
                'chatable_type' => Collection::class,
                'user_id' => $source->collection->team?->user_id,
            ]);
            $source->update([
                'chat_id' => $chat->id,
            ]);
        }

        return $source->refresh();
    }

    public function getUserId(Collection $collection): ?int
    {
        if (auth()->check()) {
            return auth()->user()->id;
        }

        return $collection->team?->user_id;
    }

    public function ifNotActionRequired(string $results): bool
    {
        // @NOTE llms sometimes do not return the right
        // string for example.
        // false becomes false, "false" or "False" etc.

        $results = str($results)
            ->trim()
            ->lower()
            ->remove('"')
            ->remove("'")
            ->toString();

        return $results == 'false';
    }
}
