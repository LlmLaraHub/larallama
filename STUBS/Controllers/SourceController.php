<?php

namespace App\Http\Controllers\Sources;

use App\Domains\Sources\SourceTypeEnum;
use App\Http\Controllers\BaseSourceController;
use App\Models\Collection;
use App\Models\Source;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class [RESOURCE_CLASS_NAME]Controller extends BaseSourceController
{

    protected SourceTypeEnum $sourceTypeEnum = SourceTypeEnum::[RESOURCE_CLASS_NAME];

    protected string $edit_path = 'Sources/[RESOURCE_CLASS_NAME]/Edit';

    protected string $show_path = 'Sources/[RESOURCE_CLASS_NAME]/Show';

    protected string $create_path = 'Sources/[RESOURCE_CLASS_NAME]/Create';

    protected string $info = '[RESOURCE_DESCRIPTION]';

    protected string $type = '[RESOURCE_TITLE_NAME]';


}
