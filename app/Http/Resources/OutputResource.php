<?php

namespace App\Http\Resources;

use App\Domains\Outputs\OutputTypeEnum;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OutputResource extends JsonResource
{
    use HelperTrait;

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $recurring = $this->getRecurring();
        $lastRun = $this->getLastRun();
        $url = null;

        if ($this->type === OutputTypeEnum::WebPage) {
            $url = route('collections.outputs.web_page.show', [
                'output' => $this->slug,
            ]);
        } elseif ($this->type === OutputTypeEnum::ApiOutput) {
            $url = route('collections.outputs.api_output.api', [
                'output' => $this->id,
            ]);
        }

        return [
            'id' => $this->id,
            'title' => $this->title,
            'type' => $this->type->value,
            'type_formatted' => str($this->type->name)->headline()->toString(),
            'collection_id' => $this->collection_id,
            'summary' => $this->summary,
            'summary_truncated' => str($this->summary)->limit(128)->markdown(),
            'active' => $this->active,
            'public' => $this->public,
            'recurring' => $recurring,
            'meta_data' => $this->meta_data,
            'last_run' => $lastRun,
            'slug' => $this->slug,
            'url' => $url,
        ];
    }
}
