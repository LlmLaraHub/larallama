<?php

namespace App\Domains\Generators\Source;

use App\Domains\Generators\Base;
use App\Domains\Generators\BaseRepository;
use Facades\App\Domains\Generators\TokenReplacer;
use Illuminate\Support\Facades\File;

class SourceTypeEnum extends Base
{
    public function handle(BaseRepository $generatorRepository): void
    {
        $this->generatorRepository = $generatorRepository;
        $sourcePath = base_path('app/Domains/Sources/SourceTypeEnum.php');
        $sourceOriginal = File::get($sourcePath);
        $token = "case [RESOURCE_NAME] = '[RESOURCE_KEY]'";
        $sourceTransformed = TokenReplacer::handle($generatorRepository, $token);
        $sourceTransformed = sprintf("%s;\n    //leave for scripting\n", $sourceTransformed);
        $sourceTransformed = str($sourceOriginal)
            ->replace('//leave for scripting', $sourceTransformed)
            ->toString();

        File::put($sourcePath, $sourceTransformed);
    }
}
