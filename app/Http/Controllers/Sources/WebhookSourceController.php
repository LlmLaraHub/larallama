<?php

namespace App\Http\Controllers\Sources;

use App\Domains\Prompts\Transformers\GithubTransformer;
use App\Domains\Sources\SourceTypeEnum;
use App\Domains\Sources\WebhookSource;
use App\Http\Controllers\BaseSourceController;
use App\Jobs\WebhookSourceJob;
use App\Models\Collection;
use App\Models\Source;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class WebhookSourceController extends BaseSourceController
{
    protected SourceTypeEnum $sourceTypeEnum = SourceTypeEnum::WebhookSource;

    protected string $edit_path = 'Sources/WebhookSource/Edit';

    protected string $show_path = 'Sources/WebhookSource/Show';

    protected string $create_path = 'Sources/WebhookSource/Create';

    protected string $info = 'For taking webhooks from sites like GitHub';

    protected string $type = 'Webhook Source';

    protected function makeSource(array $validated, Collection $collection): void
    {
        Source::create([
            'title' => $validated['title'],
            'details' => $validated['details'],
            'recurring' => $validated['recurring'],
            'active' => $validated['active'],
            'collection_id' => $collection->id,
            'slug' => str(Str::random(16))->toString(),
            'type' => $this->sourceTypeEnum,
            'meta_data' => [],
            'secrets' => $validated['secrets'],
        ]
        );
    }

    public function api(Source $source)
    {
        try {
            Log::info('[LaraChain] - WebhookSourceController', [
                'source' => $source->id,
            ]);

            //put_fixture("github_payload_real.json", request()->all());

            $webhookSource = (new WebhookSource())->payload(request()->all());

            WebhookSourceJob::dispatch(
                $webhookSource,
                $source
            );

            return response()->json(['message' => 'ok']);
        } catch (\Exception $e) {
            Log::error('Error running WebhookSource', [
                'error' => $e->getMessage(),
            ]);

            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    public function getPrompts(): array
    {
        return [
            'email' => GithubTransformer::prompt('[CONTEXT]'),
        ];
    }
}
