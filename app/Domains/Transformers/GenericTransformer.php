<?php

namespace App\Domains\Transformers;

use App\Domains\Sources\BaseSource;
use Illuminate\Support\Facades\Log;

class GenericTransformer extends BaseTransformer
{
    public TypeEnum $type = TypeEnum::GenericTransformer;

    public function transform(
        BaseSource $baseSource): BaseSource
    {

        Log::info('[LaraChain] Starting Generic Transformer ', [
            'source' => $baseSource->source->id,
        ]);

        $this->baseSource = $baseSource;
        /**
         * TODO
         */

        return $this->baseSource;
    }
}
