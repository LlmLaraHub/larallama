<?php

namespace App\Http\Controllers\Sources;

use App\Domains\Prompts\EmailToDocumentSummary;
use App\Domains\Sources\SourceTypeEnum;
use App\Http\Controllers\BaseSourceController;
use App\Models\Collection;
use App\Models\Source;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class EmailBoxSourceController extends BaseSourceController
{
    protected SourceTypeEnum $sourceTypeEnum = SourceTypeEnum::EmailBoxSource;

    protected string $edit_path = 'Sources/EmailBoxSource/Edit';

    protected string $show_path = 'Sources/EmailBoxSource/Show';

    protected string $create_path = 'Sources/EmailBoxSource/Create';

    protected string $info = 'You can parse and email box of your choice.
    We will encrypt the info in the database.
    The system will look at that box (Inbox, etc)
    and only parse unread emails.';

    protected string $type = 'Email Box Source';

    protected function makeSource(array $validated, Collection $collection): void
    {
        Source::create([
            'title' => $validated['title'],
            'details' => $validated['details'],
            'recurring' => $validated['recurring'],
            'active' => $validated['active'],
            'collection_id' => $collection->id,
            'type' => $this->sourceTypeEnum,
            'slug' => str(Str::random(12))->remove('+')->toString(),
            'meta_data' => [],
            'secrets' => [
                'username' => data_get($validated, 'secrets.username', null),
                'password' => data_get($validated, 'secrets.password', null),
                'host' => data_get($validated, 'secrets.host', null),
                'port' => data_get($validated, 'secrets.port', '993'),
                'protocol' => data_get($validated, 'secrets.protocol', 'imap'),
                'encryption' => data_get($validated, 'secrets.encryption', 'ssl'),
                'delete' => data_get($validated, 'secrets.delete', false),
                'email_box' => data_get($validated, 'secrets.email_box', null),
            ],
        ]);
    }

    protected function updateSource(Source $source, array $validated): void
    {
        Log::info('[LaraChain] - Updating Email Box Source', [
            'source' => $source->toArray(),
        ]);
        $secrets = [
            'username' => data_get($validated, 'secrets.username', null),
            'password' => data_get($validated, 'secrets.password', null),
            'host' => data_get($validated, 'secrets.host', null),
            'port' => data_get($validated, 'secrets.port', '465'),
            'protocol' => data_get($validated, 'secrets.protocol', 'imap'),
            'encryption' => data_get($validated, 'secrets.encryption', 'ssl'),
            'delete' => data_get($validated, 'secrets.delete', false),
            'email_box' => data_get($validated, 'secrets.email_box', null),
        ];

        $source->secrets = $secrets;

        $source->updateQuietly();

        $source->update([
            'title' => $validated['title'],
            'details' => $validated['details'],
            'recurring' => $validated['recurring'],
            'active' => $validated['active'],
        ]);
    }

    public function getPrompts(): array
    {
        return [
            'summarize_email' => EmailToDocumentSummary::prompt('[CONTEXT]'),
        ];
    }
}
