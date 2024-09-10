<?php

namespace App\Http\Controllers;

use App\Domains\Chat\UiStatusEnum;
use App\Domains\Projects\Prompts\CampaignPromptTemplate;
use App\Domains\Projects\StatusEnum;
use App\Http\Resources\ChatResource;
use App\Http\Resources\MessageResource;
use App\Http\Resources\ProjectResource;
use App\Http\Resources\ProjectResourceShow;
use App\Models\Chat;
use App\Models\Project;
use Facades\App\Domains\Projects\KickOffProject;
use Facades\App\Domains\Projects\Orchestrate;

class ProjectController extends Controller
{
    public function index()
    {
        $projects = ProjectResource::collection(
            Project::where(
                'team_id',
                auth()->user()->current_team_id
            )->paginate()
        );

        return inertia('Projects/Index', [
            'projects' => $projects,
        ]);
    }

    public function create()
    {
        return inertia('Projects/Create', [
            'content_start' => [
                [
                    'key' => 'Campaign Template',
                    'content' => CampaignPromptTemplate::prompt(),
                    'system_prompt' => CampaignPromptTemplate::systemPrompt(),
                ],
            ],
            'statuses' => StatusEnum::selectOptions(),
        ]);
    }

    public function store()
    {
        $validated = request()->validate([
            'name' => 'required',
            'system_prompt' => 'required',
            'start_date' => 'required',
            'end_date' => 'required',
            'status' => 'required',
            'content' => 'required',
            'chat_driver' => 'required',
            'embedding_driver' => 'required',
        ]);

        $chat_driver = $validated['chat_driver'];
        $embedding_driver = $validated['embedding_driver'];

        unset($validated['chat_driver'], $validated['embedding_driver']);

        $validated['team_id'] = auth()->user()->current_team_id;
        $project = Project::create($validated);

        $chat = Chat::create([
            'chatable_id' => $project->id,
            'chatable_type' => Project::class,
            'chat_driver' => $chat_driver,
            'user_id' => auth()->user()->id,
            'embedding_driver' => $embedding_driver,
        ]);

        return to_route('projects.showWithChat', [
            'project' => $project,
            'chat' => $chat,
        ]);
    }

    public function show(Project $project)
    {
        $chat = $project->chats()->latest()->first();

        return to_route('projects.showWithChat', [
            'project' => $project,
            'chat' => $chat,
        ]);
    }

    public function showWithChat(Project $project, Chat $chat)
    {

        return inertia('Projects/Show', [
            'project' => new ProjectResourceShow($project),
            'chat' => new ChatResource($chat),
            'messages' => MessageResource::collection($chat->messages()
                ->notSystem()
                ->notTool()
                ->latest()
                ->paginate(3)),
        ]);
    }

    public function chat(Project $project, Chat $chat)
    {
        $validated = request()->validate([
            'input' => 'required',
        ]);

        $chat->update([
            'chat_status' => UiStatusEnum::InProgress->value,
        ]);

        Orchestrate::handle(
            chat: $chat,
            prompt: $validated['input'],
            systemPrompt: $project->getSystemPrompt()
        );

        $chat->update([
            'chat_status' => UiStatusEnum::Complete->value,
        ]);

        request()->session()->flash('flash.banner', 'Chat Complete');

        return back();
    }

    public function edit(Project $project)
    {
        return inertia('Projects/Edit', [
            'statuses' => StatusEnum::selectOptions(),
            'project' => new ProjectResource($project),
        ]);
    }

    public function update(Project $project)
    {
        $validated = request()->validate([
            'name' => 'required',
            'start_date' => 'required',
            'end_date' => 'required',
            'system_prompt' => 'required',
            'status' => 'required',
            'content' => 'required',
        ]);

        $project->update($validated);

        request()->session()->flash('flash.banner', 'Updated');

        return back();
    }

    public function destroy(Project $project)
    {
        $project->delete();

        return redirect()->route('projects.index');
    }

    public function kickOff(Project $project)
    {
        KickOffProject::handle($project);
        request()->session()->flash('flash.banner', 'Done!');

        return back();
    }
}
