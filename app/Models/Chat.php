<?php

namespace App\Models;

use App\Domains\Chat\MetaDataDto;
use App\Domains\Chat\ToolsDto;
use App\Domains\Messages\RoleEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\DB;
use LlmLaraHub\LlmDriver\HasDrivers;
use LlmLaraHub\LlmDriver\Requests\MessageInDto;
use OpenAI\Laravel\Facades\OpenAI;

/**
 * @property mixed $chatable;
 * @property string $session_id;
 */
class Chat extends Model implements HasDrivers
{
    use HasFactory;

    protected $guarded = [];

    public function getDriver(): string
    {

        return $this->chatable->getDriver();
    }

    public function getSummary(): string
    {
        return $this->chatable->description;
    }

    public function getId(): int
    {
        return $this->chatable_id;
    }

    public function getType(): string
    {
        return $this->chatable_type;
    }

    public function getChat(): ?Chat
    {
        return $this;
    }

    public function getChatable(): HasDrivers
    {
        return $this->chatable;
    }

    public function getEmbeddingDriver(): string
    {
        return $this->chatable->getEmbeddingDriver();
    }

    protected function createSystemMessageIfNeeded(string $systemPrompt): void
    {
        if ($this->messages()->count() == 0) {

            $this->messages()->create(
                [
                    'body' => $systemPrompt,
                    'in_out' => false,
                    'role' => RoleEnum::System,
                    'created_at' => now(),
                    'updated_at' => now(),
                    'chat_id' => $this->id,
                    'is_chat_ignored' => true,
                ]);
        }
    }

    /* -----------------------------------------------------------------
     |  Methods
     | -----------------------------------------------------------------
     */
    /**
     * Save the input message of the user
     */
    public function addInput(string $message,
        RoleEnum $role = RoleEnum::User,
        ?string $systemPrompt = null,
        bool $show_in_thread = true,
        ?MetaDataDto $meta_data = null,
        ?ToolsDto $tools = null): Message
    {
        if (! $meta_data) {
            $meta_data = MetaDataDto::from([]);
        }

        return DB::transaction(function () use ($message, $role, $tools, $systemPrompt, $show_in_thread, $meta_data) {

            if ($systemPrompt) {
                $this->createSystemMessageIfNeeded($systemPrompt);
            }

            return $this->messages()->create(
                [
                    'body' => $message,
                    'role' => $role,
                    'in_out' => ($role === RoleEnum::User) ? true : false,
                    'created_at' => now(),
                    'updated_at' => now(),
                    'chat_id' => $this->id,
                    'is_chat_ignored' => ! $show_in_thread,
                    'meta_data' => $meta_data,
                    'tool_name' => $meta_data->tool,
                    'tool_id' => $meta_data->tool_id,
                    'driver' => $meta_data->driver,
                    'tools' => $tools,
                ]);
        });

    }

    /**
     * Save the output message of the bot
     */
    public function addOutput(string $request): Message
    {
        $message = $this->getAiResponse($request);

        return $this->addInput($message, RoleEnum::Assistant);
    }

    /**
     * Get the response from the AI
     */
    public function getAiResponse($input)
    {
        if (! $input || $input == '') {
            return 'Please enter a message';
        }

        if (str_starts_with($input, '/image')) {
            return $this->getOpenAiImage();
        }

        return $this->getChatResponse();
    }

    public function getChatResponse(int $limit = 5): array
    {
        $latestMessages = $this->messages()
            ->orderBy('id', 'desc')
            ->get();

        $latestMessagesArray = [];

        foreach ($latestMessages as $message) {
            $latestMessagesArray[] = MessageInDto::from([
                'role' => $message->role->value, 'content' => $message->compressed_body,
            ]);
        }

        return array_reverse($latestMessagesArray);

    }

    /**
     * Get response chat from OpenAI
     */
    public function getOpenAiImage(): string
    {
        $lastMessage = $this->messages()->latest()->first();
        $result = OpenAI::images()->create(['prompt' => $lastMessage->body, 'n' => 1, 'size' => '256x256', 'response_format' => 'b64_json']);

        return '<img src="data:image/png;base64, '.$result->data[0]->b64_json.'" loading="lazy" />';
    }

    public function chatable(): MorphTo
    {
        return $this->morphTo();
    }

    public function latest_messages(): HasMany
    {
        return $this->hasMany(Message::class)->where('is_chat_ignored', false)->oldest();
    }

    /**
     * Chat has many messages.
     */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function firstOrCreateUsingOutput(Output $output): Chat
    {
        $sessionId = session()->getId();

        return Chat::firstOrCreate([
            'session_id' => $sessionId,
        ], [
            'title' => 'Chat with Output '.$output->title,
            'chatable_id' => $output->collection->id,
            'chatable_type' => Collection::class,
            'user_id' => $output->getUserId(),
        ]);
    }
}
