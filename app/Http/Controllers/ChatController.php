<?php

namespace App\Http\Controllers;

use App\Domains\Messages\RoleEnum;
use App\Http\Resources\ChatResource;
use App\Http\Resources\CollectionResource;
use App\Http\Resources\MessageResource;
use App\LlmDriver\LlmDriverFacade;
use App\Models\Chat;
use App\Models\Collection;
use App\LlmDriver\Responses\CompletionResponse;

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
            'messages' => MessageResource::collection($chat->messages),
        ]);
    }

    public function chat(Chat $chat)
    {
        $validated = request()->validate([
            'input' => 'required|string',
        ]);

        $chat->addInput($validated['input'], RoleEnum::User, $chat->chatable->systemPrompt());

        $latestMessagesArray = $chat->getChatResponse();

        put_fixture('chat_messages.json', $latestMessagesArray);

        /** @var CompletionResponse $response */
        $response = LlmDriverFacade::driver(
            $chat->chatable->getDriver()
        )->chat($latestMessagesArray);

        $chat->addInput($response->content, RoleEnum::Assistant);

        return response()->json(['message' => $response->content]);
    }
}
