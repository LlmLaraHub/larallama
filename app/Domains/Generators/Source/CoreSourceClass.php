<?php

namespace App\Domains\Generators\Source;

use App\Domains\Generators\Base;
use App\Domains\Generators\BaseRepository;

class CoreSourceClass extends Base
{
    protected string $generatorName = 'Source';

    public function handle(BaseRepository $generatorRepository): void
    {
        $this->generatorRepository = $generatorRepository;

        $this->makeCoreClass();
        $this->makeCoreTest();
    }
}
