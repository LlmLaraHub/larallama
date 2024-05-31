<?php

namespace App\Domains\Generators\Source;

use App\Domains\Generators\BaseRepository;
use Facades\App\Domains\Generators\Source\ControllerSource;
use Facades\App\Domains\Generators\Source\CoreSourceClass;
use Facades\App\Domains\Generators\Source\RoutesSource;
use Facades\App\Domains\Generators\Source\SourceTypeEnum;
use Facades\App\Domains\Generators\Source\VueSource;

class GeneratorRepository extends BaseRepository
{
    public function run(): self
    {
        ControllerSource::handle($this);
        VueSource::handle($this);
        RoutesSource::handle($this);
        SourceTypeEnum::handle($this);
        CoreSourceClass::handle($this);

        return $this;
    }
}
