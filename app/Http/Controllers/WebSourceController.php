<?php

namespace App\Http\Controllers;

use App\Domains\Prompts\AnonymousChat;
use App\Domains\Recurring\RecurringTypeEnum;
use App\Domains\Sources\SourceTypeEnum;
use App\Http\Resources\CollectionResource;
use App\Http\Resources\PublicOutputResource;
use App\Models\Collection;
use App\Models\Output;
use App\Models\Source;
use Illuminate\Support\Arr;
use LlmLaraHub\LlmDriver\Requests\MessageInDto;

class WebSourceController extends BaseSourceController
{



}
