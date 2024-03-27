<?php

namespace App\Http\Controllers;

use App\Http\Resources\ChatResource;
use App\Http\Resources\CollectionResource;
use App\Models\Chat;
use App\Models\Collection;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function storeCollectionChat(Collection $collection) {
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

    public function showCollectionChat(Collection $collection, Chat $chat) {

        return inertia('Collection/Chat', [
            'collection' => new CollectionResource($collection),
            'chat' => new ChatResource($chat),
        ]);

    }
}
