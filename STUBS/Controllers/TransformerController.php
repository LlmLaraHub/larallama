<?php

namespace App\Http\Controllers\Transformers;

use App\Models\Project;
use App\Models\Transformer;
use App\Transformer\TransformerEnum;

class [RESOURCE_CLASS_NAME]TransformerController extends BaseTransformerController
{

    public function create(Project $project)
    {
        Transformer::create([
            'type' => TransformerEnum::[RESOURCE_CLASS_NAME],
            'order' => $project->transformers->count() + 1,
            'project_id' => $project->id,
        ]);

        request()->session()->flash('flash.banner', 'Created, you can sort the order using drag and drop');

        return to_route('projects.show', ['project' => $project->id]);
    }

    public function store(Project $project)
    {
        // TODO: Implement store() method.
    }

    public function update(Project $project, Transformer $transformer)
    {
        // TODO: Implement update() method.
    }
}
