<?php

namespace App\Models;

use App\Domains\Chat\MetaDataDto;
use App\Domains\Chat\ToolsDto;
use App\Domains\Messages\RoleEnum;
use App\Events\ChatUiUpdateEvent;
use App\Events\MessageCreatedEvent;
use App\Jobs\OrchestrateJob;
use App\Jobs\SimpleSearchAndSummarizeOrchestrateJob;
use Facades\App\Domains\Tokenizer\Templatizer;
use Illuminate\Bus\Batch;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;
use LlmLaraHub\LlmDriver\Functions\ToolTypes;
use LlmLaraHub\LlmDriver\HasDrivers;
use LlmLaraHub\LlmDriver\HasDriversTrait;
use LlmLaraHub\LlmDriver\LlmDriverFacade;

class Message extends Model implements HasDrivers
{
    use HasDriversTrait;
    use HasFactory;

    public $guarded = [];

    protected $dispatchesEvents = [
        'created' => MessageCreatedEvent::class,
    ];

    protected $casts = [
        'role' => RoleEnum::class,
        'tools' => ToolsDto::class,
        'meta_data' => MetaDataDto::class,
        'args' => 'array',
        'in_out' => 'boolean',
    ];

    /**
     * Return true if the message is from the user.
     */
    public function getFromUserAttribute(): bool
    {
        return $this->role === RoleEnum::User;
    }

    /**
     * Return true if the message is from the AI.
     */
    public function getFromAiAttribute(): bool
    {
        return $this->role !== RoleEnum::User;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeNotSystem(Builder $query)
    {
        return $query->where('role', '!=', RoleEnum::System->value);
    }

    public function scopeNotTool(Builder $query)
    {
        return $query->where('role', '!=', RoleEnum::Tool->value);
    }

    /**
     * Return a compressed message
     */
    public function getCompressedBodyAttribute(): string
    {
        return $this->compressMessage($this->body);
    }

    /**
     * Compress a message
     */
    public function compressMessage($message): array|string|null
    {
        if (! config('temp.compressed_messages')) {
            return $message;
        }

        // Remove spaces
        $body = str_replace(' ', '', $message);

        // Remove punctuation
        return preg_replace('/\p{P}/', '', $body);

    }

    public function report(): HasOne
    {
        return $this->hasOne(Report::class);
    }

    /**
     * Return the chat that the message belongs to.
     */
    public function chat(): BelongsTo
    {
        return $this->belongsTo(Chat::class);
    }

    public function message_document_references(): HasMany
    {
        return $this->hasMany(MessageDocumentReference::class);
    }

    public function prompt_histories(): HasMany
    {
        return $this->hasMany(PromptHistory::class);
    }

    public function getLatestMessages(): array
    {
        return $this->chat->getChatResponse();
    }

    public function getFilter(): ?Filter
    {
        $filter = data_get($this->meta_data, 'filter');

        if ($filter) {
            $filter = Filter::findOrFail($filter);
        }

        return $filter;
    }

    public function getPrompt(
        bool $appendContext = false
    ): string {
        /**
         * Fix up dates and stuff
         * that are automatic tokens
         */
        $body = $this->body;

        $body = Templatizer::appendContext($appendContext)
            ->handle($body);

        return $body;
    }

    public function getRawBody(): string
    {
        return $this->body;
    }

    public function getContent(): string
    {
        return $this->body;
    }

    public function getDriver(): string
    {
        return $this->chat->getDriver();
    }

    public function getEmbeddingDriver(): string
    {
        return $this->chat->getEmbeddingDriver();
    }

    public function getSummary(): string
    {
        return $this->chat->getSummary();
    }

    public function getId(): int
    {
        return $this->chat->getId();
    }

    public function getType(): string
    {
        return $this->chat->getType();
    }

    public function getChatable(): HasDrivers
    {
        return $this->chat->getChatable();
    }

    public function getChat(): ?Chat
    {
        return $this->chat;
    }

    public function getReferenceCollection(): ?Collection
    {
        $id = data_get($this->meta_data, 'reference_collection_id', null);

        if ($id) {
            return Collection::find($id);
        }

        return null;
    }

    public function reRun(): void
    {
        $assistantResponse = $this;

        $userRequest = Message::where('role', 'user')
            ->where('chat_id', '=', $this->chat_id)
            ->where('id', '<', $assistantResponse->id)
            ->orderBy('id', 'asc')
            ->firstOrFail();

        $assistantResponse->delete();

        $userRequest->run();
    }

    public function run(): void
    {
        $message = $this;

        $chat = $message->getChat();

        notify_ui($chat, 'Working on it!');

        $meta_data = $message->meta_data;
        $meta_data->driver = $chat->getDriver();
        $message->updateQuietly(['meta_data' => $meta_data]);

        if ($message->meta_data?->tool === 'chat') {
            Log::info('[LaraChain] Running Simple Completion');

            $messages = $chat->getChatResponse();
            $response = LlmDriverFacade::driver($chat->getDriver())
                ->setToolType(ToolTypes::NoFunction)
                ->chat($messages);
            $response = $response->content;

            $chat->addInput(
                message: $response,
                role: RoleEnum::Assistant,
                show_in_thread: true);

            notify_ui_complete($chat);
        } elseif ($message->meta_data?->tool) {
            /**
             * @NOTE
             * Quick win area for Ollama
             */
            Log::info('[LaraChain] Running Tool that was chosen');

            /** @phpstan-ignore-next-line */
            $tool = $message->meta_data?->tool;

            $this->batchJob([
                new OrchestrateJob($chat, $message),
            ], $chat, $tool);

        } elseif (LlmDriverFacade::driver($chat->getDriver())->hasFunctions()) {
            Log::info('[LaraChain] Running Orchestrate added to queue');
            $this->batchJob([
                new OrchestrateJob($chat, $message),
            ], $chat, 'orchestrate');
        } else {
            Log::info('[LaraChain] Simple Search and Summarize added to queue');
            $this->batchJob([
                new SimpleSearchAndSummarizeOrchestrateJob($message),
            ], $chat, 'simple_search_and_summarize');
        }

    }

    protected function batchJob(array $jobs, Chat $chat, string $function): void
    {
        $driver = $chat->getDriver();
        Bus::batch($jobs)
            ->name("Orchestrate Chat - {$chat->id} {$function} {$driver}")
            ->then(function (Batch $batch) use ($chat) {
                ChatUiUpdateEvent::dispatch(
                    $chat->getChatable(),
                    $chat,
                    \App\Domains\Chat\UiStatusEnum::Complete->name
                );
            })
            ->allowFailures()
            ->dispatch();
    }
}
