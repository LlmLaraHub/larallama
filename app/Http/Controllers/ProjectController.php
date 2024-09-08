<?php

namespace App\Http\Controllers;

use App\Domains\Projects\Prompts\CampaignPromptTemplate;
use App\Domains\Projects\StatusEnum;
use App\Http\Resources\ProjectResource;
use App\Http\Resources\MessageResource;
use App\Http\Resources\ProjectResourceShow;
use App\Models\Project;
use Illuminate\Http\Request;

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
                    'key' => "Campaign Template",
                    'content' => CampaignPromptTemplate::prompt(),
                ]
            ],
            'statuses' => StatusEnum::selectOptions()
        ]);
    }

    public function store()
    {
        $validated = request()->validate([
            'name' => 'required',
            'start_date' => 'required',
            'end_date' => 'required',
            'status' => 'required',
            'content' => 'required'
        ]);

        $validated['team_id'] = auth()->user()->current_team_id;
        $Project = Project::create($validated);

        return redirect()->route('projects.show', $Project);
    }

    public function show(Project $project)
    {
        $chat = $project->chats?->first();

        if(!$chat?->id) {
            $chat = $project->chats()->create([
                'chatable_id' => $project->id,
                'chatable_type' => Project::class,
                'user_id' => auth()->user()->id,
            ]);
        }

        return inertia('Projects/Show', [
            'project' => new ProjectResourceShow($project),
            'messages' => MessageResource::collection($chat->messages()
                ->latest()
                ->paginate(3)),
        ]);
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
