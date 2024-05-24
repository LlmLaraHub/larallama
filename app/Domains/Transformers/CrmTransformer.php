<?php

namespace App\Domains\Transformers;

use App\Domains\Transformers\BaseTransformer;

class CrmTransformer extends BaseTransformer
{

    public TypeEnum $type = TypeEnum::CrmTransformer;
}
