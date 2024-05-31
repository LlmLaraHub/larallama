<?php

namespace App\Domains\Generators\Source;

use App\Domains\Generators\BaseRepository;
use Facades\App\Domains\Generators\Source\ControllerSource;
use Facades\App\Domains\Generators\Source\VueSource;
use Facades\App\Domains\Generators\Source\RoutesSource;
//use Facades\App\Generators\Source\EnumSource;
//use Facades\App\Generators\Source\LarachainConfigSource;
//use Facades\App\Generators\Source\RoutesSource;
//use Facades\App\Generators\Source\SourceClassTransformer;
//use Facades\App\Generators\Source\VueSource;

class GeneratorRepository extends BaseRepository
{
    public function run(): self
    {
        ControllerSource::handle($this);
        VueSource::handle($this);
        RoutesSource::handle($this);
//        EnumSource::handle($this);
//        SourceClassTransformer::handle($this);

        return $this;
    }
}
