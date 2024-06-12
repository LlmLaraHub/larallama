<?php

namespace App\Http\Controllers;

use App\Domains\Messages\RoleEnum;
use App\Http\Resources\ChatResource;
use App\Http\Resources\CollectionResource;
use App\Http\Resources\FilterResource;
use App\Http\Resources\MessageResource;
use App\Jobs\OrchestrateJob;
use App\Jobs\SimpleSearchAndSummarizeOrchestrateJob;
use App\Models\Chat;
use App\Models\Collection;
use App\Models\Filter;
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
            'chats' => ChatResource::collection($collection->chats()->latest()->paginate(20)),
            'filters' => FilterResource::collection($collection->filters),
            'system_prompt' => $collection->systemPrompt(),
            'settings' => [
                'supports_functions' => LlmDriverFacade::driver($chat->getDriver())->hasFunctions(),
            ],
            'messages' => MessageResource::collection($chat->latest_messages),
        ]);
    }

    public function chat(Chat $chat)
    {
        $validated = request()->validate([
            'input' => 'required|string',
            'completion' => 'boolean',
            'tool' => ['nullable', 'string'],
            'filter' => ['nullable', 'integer'],
        ]);

        try {
            Log::info('Request', request()->toArray());

            $chat->addInput(
                message: $validated['input'],
                role: RoleEnum::User,
                show_in_thread: true);

            $messagesArray = [];

            $messagesArray[] = MessageInDto::from([
                'content' => $validated['input'],
                'role' => 'user',
            ]);

            $filter = data_get($validated, 'filter', null);

            if ($filter) {
                $filter = Filter::find($filter);
            }

            if (data_get($validated, 'tool', null) === 'completion') {
                Log::info('[LaraChain] Running Simple Completion');
                $prompt = $validated['input'];

                notify_ui($chat, 'We are running a completion back shortly');

                $messages = $chat->getChatResponse();
                $response = LlmDriverFacade::driver($chat->getDriver())->chat($messages);
                $response = $response->content;

                $chat->addInput(
                    message: $response,
                    role: RoleEnum::Assistant,
                    show_in_thread: true);

            } elseif (data_get($validated, 'tool', null) === 'standards_checker') {
                Log::info('[LaraChain] Running Standards Checker');
                notify_ui($chat, 'Running Standards Checker');
                /**
                 * @TODO
                 * Move this into OrchestrateJob
                 */
                OrchestrateJob::dispatch($messagesArray, $chat, $filter, 'standards_checker');
            } elseif (LlmDriverFacade::driver($chat->getDriver())->hasFunctions()) {
                Log::info('[LaraChain] Running Orchestrate added to queue');
                OrchestrateJob::dispatch($messagesArray, $chat, $filter);
            } else {
                Log::info('[LaraChain] Simple Search and Summarize added to queue');
                SimpleSearchAndSummarizeOrchestrateJob::dispatch($validated['input'], $chat, $filter);
            }

            notify_ui($chat, 'Working on it!');

            return response()->json(['message' => 'ok']);
        } catch (\Exception $e) {
            Log::error($e);

            return response()->json(['message' => $e->getMessage()], 400);
        }
    }
}
