<?php

namespace App\Http\Controllers;

use App\Domains\Sources\SourceTypeEnum;
use Illuminate\Http\Request;

class AssistantEmailBoxSourceController extends BaseSourceController
{
    protected SourceTypeEnum $sourceTypeEnum = SourceTypeEnum::EmailSource;

    protected string $edit_path = 'Sources/EmailSource/Edit';

    protected string $show_path = 'Sources/EmailSource/Show';

    protected string $create_path = 'Sources/EmailSource/Create';

}
