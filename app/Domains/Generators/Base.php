<?php

namespace App\Domains\Generators;

use Illuminate\Support\Facades\File;

use Facades\App\Domains\Generators\TokenReplacer;

abstract class Base
{
    protected string $generatorName = 'Outbound';

    protected string $plural = 's';

    protected BaseRepository $generatorRepository;

    public function handle(BaseRepository $generatorRepository): void
    {
        $this->generatorRepository = $generatorRepository;

        $this->makeController();
        $this->makeTest();
    }

    protected function getContents(string $relativePathAndFile): string
    {
        $content = $this->generatorRepository->getRootPathOrStubs().$relativePathAndFile;

        return File::get($content);
    }

    protected function makeTest()
    {
        $generatorNameAndPath = sprintf('/Tests/%sControllerTest.php', $this->generatorName);
        $content = $this->getContents($generatorNameAndPath);
        $transformed = TokenReplacer::handle($this->generatorRepository, $content);
        $name = sprintf('%sControllerTest.php',
            $this->generatorRepository->name,
        );
        $basePath = base_path('tests/Feature/Http/Controllers/');
        File::makeDirectory($basePath, 0755, true, true);
        $destination = $basePath.$name;
        $this->generatorRepository->putFile($destination, $transformed);
    }

    protected function makeController()
    {
        $generatorNameAndPath = sprintf('Controllers/%sController.php', $this->generatorName);
        $content = $this->getContents($generatorNameAndPath);

        $transformed = TokenReplacer::handle($this->generatorRepository, $content);

        $name = sprintf('%sController.php',
            $this->generatorRepository->getClassName()
        );
        $destination = base_path(sprintf('app/Http/Controllers/%s/%s',
            str($this->generatorName)->plural()->toString(),
            $name));

        $this->generatorRepository->putFile($destination, $transformed);
    }
}
