<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BatchResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'total_jobs' => $this->total_jobs,
            'pending_jobs' => $this->pending_jobs,
            'failed_jobs' => $this->failed_jobs,
            'created_at' => Carbon::parse($this->created_at)->diffForHumans(),
            'finished_at' => ($this->finished_at) ? Carbon::parse($this->finished_at)->diffForHumans() : 'na',
            'cancelled_at' => ($this->cancelled_at) ? Carbon::parse($this->cancelled_at)->diffForHumans() : 'na',
        ];
    }
}
