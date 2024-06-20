<?php

namespace App\Http\Controllers;

use App\Domains\Outputs\OutputTypeEnum;
use App\Domains\Prompts\AnonymousChat;
use App\Domains\Prompts\EmailPrompt;
use App\Domains\Recurring\RecurringTypeEnum;
use App\Http\Resources\CollectionResource;
use App\Http\Resources\DocumentResource;
use App\Http\Resources\OutputResource;
use App\Http\Resources\PersonaResource;
use App\Http\Resources\PublicOutputResource;
use App\Models\Collection;
use App\Models\Document;
use App\Models\Output;
use App\Models\Persona;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Gate;
use LlmLaraHub\LlmDriver\Requests\MessageInDto;

class OutputController extends Controller
{
    protected OutputTypeEnum $outputTypeEnum = OutputTypeEnum::WebPage;

    protected string $edit_path = 'Outputs/WebPage/Edit';

    protected string $show_path = 'Outputs/WebPage/Show';

    protected string $create_path = 'Outputs/WebPage/Create';

    protected string $info = 'Here you can make a web page for internal or external use';

    protected string $type = 'Make a Web Page';

    public function index(Collection $collection)
    {
        $chatResource = $chatResource = $this->getChatResource($collection);

        return inertia('Outputs/Index', [
            'chat' => $chatResource,
            'prompts' => $this->getPrompts(),
            'collection' => new CollectionResource($collection),
            'documents' => DocumentResource::collection(Document::query()
                ->where('collection_id', $collection->id)
                ->latest('id')
                ->paginate(50)),
            'available_outputs' => OutputTypeEnum::getAvailable($collection),
            'outputs' => OutputResource::collection($collection->outputs()->latest()->paginate(10)),
        ]);
    }

    public function store(Collection $collection)
    {

        $validated = request()->validate($this->getValidationRules());

        $this->makeOutput($validated, $collection);

        request()->session()->flash('flash.banner', 'Output added successfully');

        return to_route('collections.outputs.index', $collection);
    }

    public function edit(Collection $collection, Output $output)
    {
        return inertia($this->edit_path, [
            'output' => $output,
            'personas' => PersonaResource::collection(Persona::orderBy('name')->get()),
            'recurring' => RecurringTypeEnum::selectOptions(),
            'collection' => new CollectionResource($collection),
            'prompts' => $this->getPrompts(),
        ]);
    }

    public function update(Collection $collection, Output $output)
    {
        $validated = request()->validate($this->getValidationRules());

        $this->updateOutput($output, $validated);

        request()->session()->flash('flash.banner', 'Updated');

        return back();
    }

    public function updateOutput(Output $output, array $validated): void
    {
        $output->update($validated);
    }

    public function show(Output $output)
    {
        if (! auth()->check() && ! $output->public) {
            abort(404);
        }

        if (! $output->active) {
            abort(404);
        }

        return inertia($this->show_path, [
            'info' => $this->info,
            'type' => $this->type,
            'output' => new PublicOutputResource($output),
            'messages' => data_get($this->getChatMessages(), 'messages', []),
        ]);
    }

    public function create(Collection $collection)
    {
        return inertia($this->create_path, [
            'info' => $this->info,
            'type' => $this->type,
            'personas' => PersonaResource::collection(Persona::all()),
            'recurring' => RecurringTypeEnum::selectOptions(),
            'collection' => new CollectionResource($collection),
            'prompts' => $this->getPrompts(),
        ]);
    }

    protected function getChatMessages(): array
    {
        $messages = request()->session()->only(['messages']);

        if (empty($messages)) {
            $messages = MessageInDto::from([
                'content' => AnonymousChat::system(),
                'role' => 'system',
                'is_ai' => true,
                'show' => false,
            ]);

            request()->session()->push('messages', $messages);
            $messages = Arr::wrap($messages);
        }

        return $messages;
    }

    protected function setChatMessages(string $input, string $role = 'user')
    {
        $this->getChatMessages();

        $messages = MessageInDto::from([
            'content' => str($input)->markdown(),
            'role' => $role,
            'is_ai' => $role !== 'user',
            'show' => $role !== 'system',
        ]);

        request()->session()->push('messages', $messages);

        return $messages;
    }

    public function delete(Output $output)
    {
        if (! Gate::allows('delete', $output)) {
            abort(403);
        }

        $collection = $output->collection;
        $output->delete();

        request()->session()->flash('flash.banner', 'Output deleted');

        return to_route('collections.outputs.index', $collection);
    }

    protected function getValidationRules(): array
    {
        return [
            'title' => 'required|string',
            'persona_id' => ['nullable', 'integer'],
            'summary' => 'required|string',
            'active' => 'boolean|nullable',
            'public' => 'boolean|nullable',
            'recurring' => 'string|nullable',
            'meta_data' => 'array|nullable',
            'secrets' => ['nullable', 'array'],
        ];
    }

    protected function makeOutput(array $validated, Collection $collection): void
    {
        Output::create([
            'title' => $validated['title'],
            'summary' => $validated['summary'],
            'collection_id' => $collection->id,
            'recurring' => data_get($validated, 'recurring', null),
            'active' => data_get($validated, 'active', false),
            'public' => data_get($validated, 'public', false),
            'type' => $this->outputTypeEnum,
            'meta_data' => data_get($validated, 'meta_data', []),
            'secrets' => data_get($validated, 'secrets', []),
        ]);
    }

    public function getPrompts(): array
    {
        return [
            //'example_prompt' => EmailPrompt::prompt('[CONTEXT]'),
        ];
    }
}
