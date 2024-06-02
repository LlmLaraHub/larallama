<?php

namespace App\Domains\Generators;

use Facades\App\Domains\Generators\TokenReplacer;
use Illuminate\Support\Facades\File;

class VueGenerator extends Base
{
    protected string $generatorName = 'Outbound';

    public function handle(BaseRepository $generatorRepository): void
    {
        $this->generatorRepository = $generatorRepository;
        $this->makeVue();
    }

    protected function makeVue()
    {
        $rootPath = base_path(sprintf('resources/js/Pages/%s/%s',
            str($this->generatorName)->plural()->toString(),
            $this->generatorRepository->getClassName()));

        File::makeDirectory(sprintf('%s/Components', $rootPath), 0755, true, true);

        $path = sprintf($this->generatorRepository->getRootPathOrStubs().'Vue/%s/%s',
            str($this->generatorName)->plural()->toString(),
            $this->generatorName);

        $files = File::allFiles($path);

        foreach ($files as $file) {
            $content = File::get($file->getPathname());
            $transformed = TokenReplacer::handle($this->generatorRepository, $content);

            if (in_array($file->getFilename(), [
                'Resources.vue', 'Card.vue',
            ])) {
                $destination = $rootPath.'/Components/'.$file->getFilename();
            } else {
                $destination = sprintf('%s/%s',
                    $rootPath,
                    $file->getFilename()
                );
            }

            $this->generatorRepository->putFile($destination, $transformed);
        }
    }
}
