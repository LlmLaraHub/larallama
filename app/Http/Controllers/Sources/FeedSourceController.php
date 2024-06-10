<?php

namespace App\Http\Controllers\Sources;

use App\Domains\Sources\SourceTypeEnum;
use App\Http\Controllers\BaseSourceController;
use App\Models\Collection;
use App\Models\Source;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class FeedSourceController extends BaseSourceController
{

    protected SourceTypeEnum $sourceTypeEnum = SourceTypeEnum::FeedSource;

    protected string $edit_path = 'Sources/FeedSource/Edit';

    protected string $show_path = 'Sources/FeedSource/Show';

    protected string $create_path = 'Sources/FeedSource/Create';

    protected string $info = 'Get Feeds from Websites and create content from them';

    protected string $type = 'Feed Source';


}
