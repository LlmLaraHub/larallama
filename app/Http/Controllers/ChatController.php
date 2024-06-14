<?php

namespace App\Http\Controllers;

use App\Domains\Messages\RoleEnum;
use App\Events\ChatUiUpdateEvent;
use App\Http\Resources\ChatResource;
use App\Http\Resources\CollectionResource;
use App\Http\Resources\FilterResource;
use App\Http\Resources\MessageResource;
use App\Http\Resources\PersonaResource;
use App\Jobs\OrchestrateJob;
use App\Jobs\SimpleSearchAndSummarizeOrchestrateJob;
use App\Models\Chat;
use App\Models\Collection;
use App\Models\Filter;
use App\Models\Persona;
use Illuminate\Bus\Batch;
use Illuminate\Support\Facades\Bus;
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
            'personas' => PersonaResource::collection(Persona::all()),
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
            'persona' => ['nullable', 'integer'],
        ]);

        try {
            Log::info('Request', request()->toArray());

            $input = $validated['input'];

            $persona = data_get($validated, 'persona', null);

            if ($persona) {
                $persona = Persona::find($persona);
                $input = $persona->wrapPromptInPersona($input);
            }

            $chat->addInput(
                message: $input,
                role: RoleEnum::User,
                show_in_thread: true);

            $messagesArray = [];

            $messagesArray[] = MessageInDto::from([
                'content' => $input,
                'role' => 'user',
            ]);

            $filter = data_get($validated, 'filter', null);

            if ($filter) {
                $filter = Filter::find($filter);
            }

            if (data_get($validated, 'tool', null) === 'completion') {
                Log::info('[LaraChain] Running Simple Completion');

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
                $this->batchJob([
                    new OrchestrateJob($messagesArray, $chat, $filter, 'standards_checker'),
                ], $chat, 'search_and_summarize');
            } elseif (LlmDriverFacade::driver($chat->getDriver())->hasFunctions()) {
                Log::info('[LaraChain] Running Orchestrate added to queue');
                $this->batchJob([
                    new OrchestrateJob($messagesArray, $chat, $filter),
                ], $chat, 'orchestrate');
            } else {
                Log::info('[LaraChain] Simple Search and Summarize added to queue');
                $this->batchJob([
                    new SimpleSearchAndSummarizeOrchestrateJob($input, $chat, $filter),
                ], $chat, 'simple_search_and_summarize');
            }

            notify_ui($chat, 'Working on it!');

            return response()->json(['message' => 'ok']);
        } catch (\Exception $e) {
            Log::error($e);

            return response()->json(['message' => $e->getMessage()], 400);
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
