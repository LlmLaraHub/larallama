<?php

namespace App\Http\Controllers;

use App\Domains\Prompts\WebSearchPrompt;
use App\Domains\Sources\SourceTypeEnum;

class WebSourceController extends BaseSourceController
{
    protected SourceTypeEnum $sourceTypeEnum = SourceTypeEnum::WebSearchSource;

    protected string $edit_path = 'Sources/WebSource/Edit';

    protected string $show_path = 'Sources/WebSource/Show';

    protected string $create_path = 'Sources/WebSource/Create';

    protected string $info = "Your Details will be used for a web search.
    for example 'news about the php' will search for news about php.
    Recurring would have it run as selected.
    All new content will become documents in your collection.
    ";

    protected string $type = 'Web Search';

    public function getPrompts(): array
    {
        return [
            'web_search' => WebSearchPrompt::prompt('[USER_INPUT]'),
        ];
    }
}
