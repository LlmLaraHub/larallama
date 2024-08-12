<?php

namespace App\Http\Controllers;

use App\Domains\Chat\DateRangesEnum;
use App\Domains\Chat\MetaDataDto;
use App\Domains\Messages\RoleEnum;
use App\Http\Resources\AudienceResource;
use App\Http\Resources\ChatResource;
use App\Http\Resources\CollectionResource;
use App\Http\Resources\FilterResource;
use App\Http\Resources\MessageResource;
use App\Http\Resources\PersonaResource;
use App\Models\Audience;
use App\Models\Chat;
use App\Models\Collection;
use App\Models\Message;
use App\Models\Persona;
use Illuminate\Support\Facades\Log;
use LlmLaraHub\LlmDriver\LlmDriverFacade;

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
            'reference_collections' => Collection::orderBy('name')
                ->select('id', 'name')
                ->get()
                ->transform(
                    /** @phpstan-ignore-next-line */
                    function ($item) {
                        return [
                            'id' => $item->id,
                            'name' => $item->name,
                        ];
                    }),
            'date_ranges' => DateRangesEnum::selectOptions(),
            'chat' => new ChatResource($chat),
            'chats' => ChatResource::collection($collection->chats()->latest()->paginate(10)),
            'filters' => FilterResource::collection($collection->filters),
            'personas' => PersonaResource::collection(Persona::all()),
            'audiences' => AudienceResource::collection(Audience::all()),
            'system_prompt' => $collection->systemPrompt(),
            'settings' => [
                'supports_functions' => LlmDriverFacade::driver($chat->getDriver())->hasFunctions(),
            ],
            'messages' => MessageResource::collection($chat->latest_messages()->limit(10)->get()),
        ]);
    }

    public function latestChatMessage(Collection $collection, Chat $chat)
    {
        return response()->json([
            'messages' => MessageResource::collection($chat->latest_messages),
        ]);
    }

    public function chat(Chat $chat)
    {
        $validated = request()->validate([
            'completion' => ['boolean'],
            'tool' => ['nullable', 'string'],
            'input' => ['required', 'string'],
            'filter' => ['nullable', 'integer'],
            'persona' => ['nullable', 'integer'],
            'date_range' => ['nullable', 'string'],
            'reference_collection_id' => ['required_if:tool,reporting_tool'],
        ]);

        try {
            Log::info('Request', request()->toArray());

            $input = $validated['input'];

            $persona = data_get($validated, 'persona', null);

            if ($persona) {
                $persona = Persona::find($persona);
                $input = $persona->wrapPromptInPersona($input);
            }

            $meta_data = MetaDataDto::from($validated);

            $message = $chat->addInput(
                message: $input,
                role: RoleEnum::User,
                show_in_thread: true,
                meta_data: $meta_data);

            $message->run();

            return response()->json(['message' => 'ok']);
        } catch (\Exception $e) {
            Log::error($e);

            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    public function deleteMessage(Message $message)
    {
        $chat = $message->chat;
        $message->delete();
        request()->session()->flash('flash.banner', 'Message deleted!');

        return to_route('chats.collection.show', [
            'collection' => $chat->getId(),
            'chat' => $chat->id,
        ]);
    }
}
