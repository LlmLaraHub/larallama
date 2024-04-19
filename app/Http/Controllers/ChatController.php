<?php

namespace App\Http\Controllers;

use App\Domains\Messages\RoleEnum;
use App\Events\ChatUpdatedEvent;
use App\Http\Resources\ChatResource;
use App\Http\Resources\CollectionResource;
use App\Http\Resources\MessageResource;
use App\Models\Chat;
use App\Models\Collection;
use Facades\LlmLaraHub\LlmDriver\Orchestrate;
use Facades\LlmLaraHub\LlmDriver\SimpleSearchAndSummarizeOrchestrate;
use Illuminate\Support\Facades\Log;
use LlmLaraHub\LlmDriver\LlmDriverFacade;
use LlmLaraHub\LlmDriver\Requests\MessageInDto;

class ChatController extends Controller
{
    public function storeCollectionChat(Collection $collection)
    {
        $chat = new Chat();
        $chat->chatable_id = $collection->id;
        $chat->chatable_type = Collection::class;
        $chat->user_id = auth()->user()->id;
        $chat->save();

        request()->session()->flash('flash.banner', 'Chat created successfully!');

        return to_route('chats.collection.show', [
            'collection' => $collection->id,
            'chat' => $chat->id,
        ]);
    }

    public function showCollectionChat(Collection $collection, Chat $chat)
    {
        return inertia('Collection/Chat', [
            'collection' => new CollectionResource($collection),
            'chat' => new ChatResource($chat),
            'system_prompt' => $collection->systemPrompt(),
            'messages' => MessageResource::collection($chat->latest_messages),
        ]);
    }

    public function chat(Chat $chat)
    {
        $validated = request()->validate([
            'input' => 'required|string',
            'completion' => 'boolean',
        ]);

        $chat->addInput(
            message: $validated['input'],
            role: RoleEnum::User,
            show_in_thread: true);

        $messagesArray = [];

        $messagesArray[] = MessageInDto::from([
            'content' => $validated['input'],
            'role' => 'user',
        ]);

        if (data_get($validated, 'completion', false)) {
            Log::info('[LaraChain] Running Simple Completion');
            $prompt = $validated['input'];
            $response = LlmDriverFacade::driver($chat->getDriver())->completion($prompt);
            $response = $response->content;
            $chat->addInput(
                message: $response,
                role: RoleEnum::Assistant,
                show_in_thread: true);
        } elseif (LlmDriverFacade::driver($chat->getDriver())->hasFunctions()) {
            Log::info('[LaraChain] Running Orchestrate');
            $response = Orchestrate::handle($messagesArray, $chat);
        } else {
            Log::info('[LaraChain] Simple Search and Summarize');
            $response = SimpleSearchAndSummarizeOrchestrate::handle($validated['input'], $chat);
        }

        ChatUpdatedEvent::dispatch($chat->chatable, $chat);

        return response()->json(['message' => $response]);
    }
}
