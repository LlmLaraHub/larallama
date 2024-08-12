<?php

namespace App\Helpers;

use App\Domains\Chat\MetaDataDto;
use App\Domains\Messages\RoleEnum;
use App\Models\Chat;
use App\Models\Collection;
use App\Models\Source;
use App\Models\SourceTask;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

trait ChatHelperTrait
{
    public function checkForChat(Source $source): Source
    {
        if (! $source->chat_id) {
            //@NOTE should I go to Source as the chatable?
            $chat = Chat::create([
                'title' => sprintf('Chat Log for Source #%s', $source->title),
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

    public function skip(Source $source, string $key): bool
    {
        if (! $source->force &&
        SourceTask::where('source_id', $source->id)->where('task_key', $key)->exists()) {
            Log::info('[LaraChain] GetWebContentJob - Skipping - already ran', [
                'source' => $source->id,
                'key' => $key,
            ]);

            return true;
        } else {
            return false;
        }
    }

    public function createSourceTask(Source $source, string $key): SourceTask
    {
        return SourceTask::updateOrCreate([
            'source_id' => $source->id,
            'task_key' => $key,
        ]);
    }

    public function addUserMessage(Source $source, string $message): void
    {
        $source->refresh()->getChat()->addInput(
            message: $message,
            role: RoleEnum::User,
            show_in_thread: true,
            meta_data: MetaDataDto::from([
                'driver' => $source->getDriver(),
                'source' => $source->title,
            ]),
        );
    }

    public function arrifyPromptResults(string $original): array
    {
        $promptResults = json_decode($original, true);

        if (is_null($promptResults)) {
            $promptResults = Arr::wrap($original);
        }

        return $promptResults;
    }
}
