<?php

namespace App\Http\Controllers;

use App\Events\ChatUpdatedEvent;
use App\Http\Resources\ChatResource;
use App\Http\Resources\CollectionResource;
use App\Http\Resources\MessageResource;
use App\LlmDriver\LlmDriverFacade;
use App\Models\Chat;
use App\Models\Collection;
use Facades\App\Domains\Messages\SearchOrSummarizeChatRepo;

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
        ]);

        //get all the functions we have

        $response = LlmDriverFacade::driver(
            $chat->chatable->getDriver()
        )->chat($validated['input']);
        //attach them the the initial request
        //see if we get results or a function request
        //if we get a function request, we run the function
        //if we get results, we return the results

        $response = SearchOrSummarizeChatRepo::search($chat, $validated['input']);

        ChatUpdatedEvent::dispatch($chat->chatable, $chat);

        return response()->json(['message' => $response]);
    }
}
