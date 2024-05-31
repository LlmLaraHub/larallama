<?php

namespace App\Domains\Generators;

class TokenReplacer
{
    public function handle(BaseRepository $generatorRepository, string $content): string
    {
        return str($content)
            ->replace([
                '[RESOURCE_KEY]',
                '[RESOURCE_NAME]',
                '[RESOURCE_TITLE_NAME]',
                '[RESOURCE_CLASS_NAME]',
                '[RESOURCE_DESCRIPTION]',
            ], [
                $generatorRepository->getKey(),
                $generatorRepository->name,
                str($generatorRepository->name)->headline(),
                str($generatorRepository->name)->studly(),
                $generatorRepository->description,
            ])->toString();
    }
}
