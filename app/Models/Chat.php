<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use OpenAI\Laravel\Facades\OpenAI;

class Chat extends Model
{
    use HasFactory;

    protected $fillable = [];

    /* -----------------------------------------------------------------
     |  Methods
     | -----------------------------------------------------------------
     */
    /**
     * Save the input message of the user
     */
    public function addInput(string $message, bool $in = true, bool $isChatIgnored = false): Message
    {

        $message = $this->messages()->create(
            [
                'body' => $message,
                'in_out' => $in,
                'created_at' => now(),
                'updated_at' => now(),
                'chat_id' => $this->id,
                'is_chat_ignored' => $isChatIgnored,
            ]);


        return $message;
    }

    /**
     * Save the output message of the bot
     */
    public function addOutput(string $request): Message
    {
        $message = $this->getAiResponse($request);

        return $this->addInput($message, false);
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

        return $this->getOpenAiChat();
    }

    /**
     * Get response chat from OpenAI
     */
    public function getOpenAiChat(int $limit = 5): string
    {
        $latestMessages = $this->messages()->latest()->limit($limit)->get()->sortBy('id');

        /**
         * Reverse the messages to preserve the order for OpenAI
         */
        $latestMessagesArray = [];
        foreach ($latestMessages as $message) {
            $latestMessagesArray[] = [
                'role' => $message->in_out ? 'user' : 'assistant', 'content' => $message->compressed_body];
        }

        $response = OpenAI::chat()->create(['model' => 'gpt-3.5-turbo', 'messages' => $latestMessagesArray]);

        return $response->choices[0]->message->content;

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


    public function chatable()
    {
        return $this->morphTo();
    }

    /* -----------------------------------------------------------------
     |  Relationships
     | -----------------------------------------------------------------
     */

    /**
     * Chat has many messages.
     */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    public function user() : BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
