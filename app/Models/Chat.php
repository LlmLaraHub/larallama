<?php

namespace App\Models;

use App\Domains\Messages\RoleEnum;
use App\LlmDriver\Requests\MessageInDto;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use OpenAI\Laravel\Facades\OpenAI;

/**
 * @property mixed $chatable;
 */
class Chat extends Model
{
    use HasFactory;

    protected $fillable = [];

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
        bool $show_in_thread = true): Message
    {

        if ($systemPrompt) {
            $this->createSystemMessageIfNeeded($systemPrompt);
        }

        $message = $this->messages()->create(
            [
                'body' => $message,
                'role' => $role,
                'in_out' => ($role === RoleEnum::User) ? true : false,
                'created_at' => now(),
                'updated_at' => now(),
                'chat_id' => $this->id,
                'is_chat_ignored' => ! $show_in_thread,
            ]);

        return $message;
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
        $latestMessages = $this->messages()->latest()->limit($limit)->get()->sortBy('id');

        $latestMessagesArray = [];

        foreach ($latestMessages as $message) {
            $latestMessagesArray[] = MessageInDto::from([
                'role' => $message->role->value, 'content' => $message->compressed_body,
            ]);
        }

        return $latestMessagesArray;

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
}
